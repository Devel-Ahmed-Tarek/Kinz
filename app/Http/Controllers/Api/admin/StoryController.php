<?php

namespace App\Http\Controllers\Api\admin;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Jobs\DeleteExpiredStories;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StoryController extends Controller
{
    // إنشاء قصة جديدة
    public function store(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:text,image,video',
            'content' => 'required',
            'background_color' => 'nullable|string',
            'voice_id' => 'nullable|exists:voices,id',
            'video_duration' => 'nullable|in:30,60,180',
            'visibility' => 'required|in:public,friends,private',
        ]);

        // Check for validation errors
        if ($validator->fails()) {
            // Return validation error messages
            return resourceApi::sendResponse(422, __('messages.validation_failed'), $validator->messages()->all());
        }

        // Create the story if validation passes
        $story = new Story();
        $story->user_id = Auth::id();
        $story->type = $request->input('type');
        $story->background_color = $request->input('background_color', null);
        $story->voice_id = $request->input('voice_id', null);
        $story->video_duration = $request->input('video_duration', null);
        $story->visibility = $request->input('visibility');
        $story->expires_at = now()->addDay(); // Expires after 24 hours

        // Upload content if it's an image or video
        if ($request->hasFile('content')) {
            $path = $request->file('content')->store('stories', 'public');
            // Store the complete URL
            $story->content = url('storage/' . $path);
        } else {
            $story->content = $request->input('content');
        }
        $story->save();
        DeleteExpiredStories::dispatch()->delay(now()->addDays(1));

        // Return success response
        return resourceApi::sendResponse(201, __('messages.story_created_successfully'), $story);
    }

    public function getFollowingUsersWithStories()
    {
        // المستخدم الحالي
        $user = auth()->user();

        // جلب معرفات المستخدمين الذين يتابعهم المستخدم الحالي
        $followingUserIds = $user->following()->pluck('id');

        // جلب القصص النشطة للمستخدمين المتابعين
        $activeStories = Story::whereIn('user_id', $followingUserIds)
            ->where('expires_at', '>', now()) // التحقق من أن القصة لم تنتهِ بعد
            ->with('user') // جلب المستخدمين المرتبطين بالقصص
            ->get();

        // إرجاع النتيجة باستخدام resourceApi
        return resourceApi::sendResponse(200, __('messages.following_users_with_active_stories_retrieved_successfully'), $activeStories);
    }

    // الإعجاب بالقصة
    public function like($id)
    {
        $story = Story::findOrFail($id);

        // التحقق مما إذا كان المستخدم قد أعجب من قبل
        if ($story->likes()->where('user_id', auth()->id())->exists()) {
            return resourceApi::sendResponse(400, __('messages.already_liked'));
        }

        // إضافة الإعجاب
        $story->likes()->create(['user_id' => auth()->id()]);

        // إرجاع استجابة النجاح
        return resourceApi::sendResponse(200, __('messages.liked_successfully'));
    }

    // الرد على القصة
    public function reply(Request $request, $id)
    {
        $story = Story::findOrFail($id);

        // التحقق من البيانات المدخلة
        $validator = Validator::make($request->all(), [
            'reply_text' => 'required|string',
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, __('messages.validation_failed'), $validator->messages()->all());
        }

        // إنشاء الرد
        $reply = $story->replies()->create([
            'user_id' => auth()->id(),
            'reply_text' => $request->input('reply_text'),
        ]);

        // إرجاع استجابة النجاح
        return resourceApi::sendResponse(201, __('messages.reply_created_successfully'), $reply);
    }

    public function report(Request $request, $id)
    {
        // العثور على القصة بناءً على معرفها
        $story = Story::findOrFail($id);

        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'report_type_id' => 'required|exists:report_types,id', // تأكد من وجود نوع البلاغ
        ]);

        // إذا فشلت عملية التحقق، نرجع رسالة خطأ
        if ($validator->fails()) {
            return ResourceApi::sendResponse(422, __('messages.validation_failed'), $validator->messages()->all());
        }

        // إنشاء تقرير
        $story->reports()->create([
            'user_id' => auth()->id(), // معرف المستخدم الذي قام بالتبليغ
            'story_id' => $story->id, // إضافة معرف القصة
            'report_type_id' => $request->input('report_type_id'), // نوع البلاغ
        ]);

        // إعادة استجابة ناجحة
        return ResourceApi::sendResponse(200, __('messages.report_submitted_successfully'));
    }

    public function GeMyStories()
    {
        $activeStories = Auth::user()->stories()->where('expires_at', '>', now())->get();
        return resourceApi::sendResponse(200, __('messages.following_users_with_active_stories_retrieved_successfully'), $activeStories);

    }

    // حذف القصة
    public function delete($id)
    {
        $story = Story::where('user_id', auth()->id())->findOrFail($id);

        // حذف القصة
        $story->delete();

        // إرجاع استجابة النجاح
        return resourceApi::sendResponse(200, __('messages.story_deleted_successfully'));
    }

}

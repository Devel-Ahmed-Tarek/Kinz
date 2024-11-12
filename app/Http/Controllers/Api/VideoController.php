<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResourceApi;
use App\Http\Controllers\Controller;
use App\Models\Hashtag;
use App\Models\Interaction;
use App\Models\SavedVideo;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{

    public function homePage(Request $request)
    {
        $user = Auth::user(); // المستخدم الحالي

        // Fetch random videos and paginate
        $randomVideos = Video::with('user')->inRandomOrder()->paginate(10);

        // Map the videos to the desired format
        $data = $randomVideos->getCollection()->map(function ($video) use ($user) {
            // Check if the current user has liked the video
            $hasLiked = $video->interactions()->where('user_id', $user->id)->where('type', 'like')->exists();

            // Check if the current user is following the video owner
            $isFollowing = $user->following()->where('user_id', $video->user->id)->exists();

            // Check if the current user has saved the video
            $isSaved = $user->savedVideos()->where('video_id', $video->id)->exists();

            return [
                'id' => $video->id,
                'title' => $video->title,
                'url' => asset($video->video_path),
                'likes_count' => $video->interactions->count(),
                'views' => $video->views,
                'description' => $video->description,
                'has_liked' => $hasLiked, // Whether the user liked the video
                'is_saved' => $isSaved, // Whether the video is in the user's saved list
                'created_at' => $video->created_at->toDateTimeString(),

                // User data (with following status)
                'user' => [
                    'id' => $video->user->id,
                    'name' => $video->user->name,
                    'username' => $video->user->username,
                    'profile_image' => $video->user->profile_image,
                    'is_following' => $isFollowing, // Whether the current user is following this user
                ],
            ];
        });

        // Return paginated response with the formatted data
        return ResourceApi::pagination($randomVideos, $data);
    }

    public function uploadVideo(Request $request)
    {
        // Validate the input data
        $validator = Validator::make($request->all(), [
            'video' => 'required|mimetypes:video/mp4,video/x-matroska|max:51200', // Max size 50MB
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return ResourceApi::sendResponse(422, __('messages.validation_errors'), $validator->messages()->all());
        }

        // Store the video temporarily
        $videoFile = $request->file('video');

        // Define the permanent path
        $permanentPath = 'videos/' . time() . '.' . $videoFile->getClientOriginalExtension();

        // Store the video directly in the permanent location
        Storage::disk('public')->put($permanentPath, file_get_contents($videoFile));

        // Create a video record with a placeholder path
        $video = Video::create([
            'user_id' => Auth::user()->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'video_path' => Storage::url($permanentPath),
        ]);

        // استخراج الهاشتاجات من الوصف
        $hashtags = $this->extractHashtags($video->description);

        foreach ($hashtags as $hashtag) {
            // تحقق مما إذا كان الهاشتاج موجوداً
            $hashtagModel = Hashtag::firstOrCreate(['tag' => $hashtag, "video_id" => $video->id]);

            // ربط الفيديو بالهاشتاج
            $video->hashtags()->attach($hashtagModel->id);
        }

        // Return a response indicating that the video is being processed
        return ResourceApi::sendResponse(201, __('messages.video_upload_progress'), $video);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $type = $request->type; // تحديد نوع الفيديوهات المطلوبة

        switch ($type) {
            case 'interacted':
                // جلب الفيديوهات التي تفاعل معها المستخدم
                $videos = Video::whereHas('interactions', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->paginate(10);
                break;

            case 'owned':
                // جلب الفيديوهات التي يملكها المستخدم
                $videos = $user->videos()->paginate(10);
                break;

            case 'saved':
                // جلب الفيديوهات المحفوظة
                $videos = $user->savedVideos()->paginate(10);
                break;

            default:
                return ResourceApi::sendResponse(400, __('messages.invalid_video_type'));
        }

        // إعداد البيانات لتضمينها في الاستجابة
        $data = $videos->map(function ($video) {
            return [
                'id' => $video->id,
                'title' => $video->title,
                'url' => asset($video->video_path), // استخدام asset للحصول على URL كامل
                'likes_count' => $video->interactions->count(),
                'views' => $video->views,
                'created_at' => $video->created_at,
                'user' => [
                    'id' => $video->user->id,
                    'name' => $video->user->name,
                    'username' => $video->user->username,
                    'profile_image' => $video->user->profile_image,
                ], // يمكن إضافته للأنواع الأخرى
                // تفاصيل أخرى حسب الحاجة
            ];
        });

        return ResourceApi::pagination($videos, $data);
    }

    // Increment video views
    public function incrementViews($id)
    {
        $video = Video::findOrFail($id);
        $video->increment('views');
        Interaction::create([
            'user_id' => Auth::user()->id,
            'video_id' => $video->id,
            'type' => 'view',
        ]);

        return ResourceApi::sendResponse(200, __('messages.video_views_incremented'), []);
    }

    // Like a video
    public function likeVideo($id)
    {
        $video = Video::findOrFail($id);
        $video->increment('likes');

        // Save interaction as like
        Interaction::create([
            'user_id' => Auth::user()->id,
            'video_id' => $video->id,
            'type' => 'like',
        ]);

        return ResourceApi::sendResponse(200, __('messages.video_liked_successfully'), []);
    }

    // Add a video to saved videos
    public function addToSavedVideos($video_id)
    {
        $user = Auth::user();
        $video = Video::findOrFail($video_id);

        // تحقق مما إذا كان الفيديو موجودًا بالفعل في قائمة المحفوظات
        if ($user->savedVideos()->where('video_id', $video->id)->exists()) {
            return ResourceApi::sendResponse(409, __('messages.video_already_saved'));
        }

        // إضافة الفيديو إلى قائمة المحفوظات
        $savedVideo = SavedVideo::create([
            'user_id' => $user->id,
            'video_id' => $video->id,
        ]);

        return ResourceApi::sendResponse(201, __('messages.video_added_to_saved'), $savedVideo);
    }

    // Remove a video from saved videos
    public function removeFromSavedVideos($video_id)
    {
        $user = Auth::user();

        $video = Video::find($video_id);

        if (!$video) {
            return ResourceApi::sendResponse(404, __('messages.video_not_found'));
        }

        $savedVideo = SavedVideo::where('user_id', $user->id)->where('video_id', $video_id)->first();

        if (!$savedVideo) {
            return ResourceApi::sendResponse(404, __('messages.video_not_in_saved'));
        }

        $savedVideo->delete();

        return ResourceApi::sendResponse(200, __('messages.video_removed_from_saved'), []);
    }

    // Update video details
    public function updateVideo(Request $request, $id)
    {
        $video = Video::findOrFail($id);

        // Ensure the user has permission to update the video
        if ($video->user_id !== Auth::user()->id) {
            return ResourceApi::sendResponse(403, __('messages.unauthorized'));
        }

        // Validate the data
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return ResourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        // Update title and description
        $video->update([
            'title' => $request->input('title', $video->title),
            'description' => $request->input('description', $video->description),
        ]);

        return ResourceApi::sendResponse(200, __('messages.video_updated_successfully'), []);
    }

    // Delete a video
    public function deleteVideo($id)
    {
        $video = Video::findOrFail($id);

        // Delete the video from storage if it exists
        if ($video->video_path) {
            Storage::disk('public')->delete($video->video_path);
        }

        // Delete the video record from the database
        $video->delete();

        return ResourceApi::sendResponse(200, __('messages.video_deleted_successfully'), []);
    }

    // Get videos of the authenticated user
    public function getUserVideos()
    {
        $user = Auth::user();

        // Retrieve the user's videos
        $videos = $user->videos()->latest()->get();

        return ResourceApi::sendResponse(200, __('messages.user_videos_retrieved_successfully'), $videos);
    }

    public function getVideosByUserId($id)
    {
        // العثور على المستخدم أو إرجاع خطأ إذا لم يتم العثور على المستخدم
        $user = User::findOrFail($id);

        // استرجاع الفيديوهات الخاصة بالمستخدم
        $videos = $user->videos()->latest()->paginate(10);

        // المستخدم الحالي الذي قام بتسجيل الدخول
        $currentUser = Auth::user();

        // تنظيم بيانات الفيديو مع تفاصيل إضافية
        $videoData = $videos->map(function ($video) use ($currentUser) {
            // Check if the current user has liked the video
            $hasLiked = $video->interactions()->where('user_id', $currentUser->id)->where('type', 'like')->exists();

            // Check if the current user is following the video owner
            $isFollowing = $currentUser->following()->where('user_id', $video->user->id)->exists();

            // Check if the current user has saved the video
            $isSaved = $currentUser->savedVideos()->where('video_id', $video->id)->exists();

            return [
                'id' => $video->id,
                'title' => $video->title,
                'url' => asset($video->video_path),
                'likes_count' => $video->interactions->count(),
                'views' => $video->views,
                'description' => $video->description,
                'has_liked' => $hasLiked, // Whether the user liked the video
                'is_saved' => $isSaved, // Whether the video is in the user's saved list
                'created_at' => $video->created_at->toDateTimeString(),

                // User data (with following status)
                'user' => [
                    'id' => $video->user->id,
                    'name' => $video->user->name,
                    'username' => $video->user->username,
                    'profile_image' => $video->user->profile_image,
                    'is_following' => $isFollowing, // Whether the current user is following this user
                ],
            ];
        });

        // إرجاع البيانات المنظمة مع رسالة نجاح
        return ResourceApi::pagination($videos, $videoData);
    }

    // Download a video
    public function downloadVideo($id)
    {
        $video = Video::findOrFail($id);
        $filePath = storage_path('app/public' . $video->video_path);

        if (!file_exists($filePath)) {
            return ResourceApi::sendResponse(404, ['en' => 'Video not found', 'ar' => 'الفيديو غير موجود']);
        }

        return response()->download($filePath);
    }

    public function show($id)
    {
        $video = Video::findOrFail($id);

        return ResourceApi::sendResponse(200, __('Video retrieved successfully'), [
            'id' => $video->id,
            'title' => $video->title,
            'description' => $video->description,
            'video_path' => $video->video_path, // This is the full URL
            'likes' => $video->likes,
            'views' => $video->views,
            'created_at' => $video->created_at,
            'user' => [
                'id' => $video->user->id,
                'name' => $video->user->name,
                'username' => $video->user->username,
                'profile_image' => $video->user->profile_image,
            ],
        ]);
    }

// Remove user interaction with a video
    public function removeInteractionLikes($videoId)
    {
        // Find the video
        $video = Video::findOrFail($videoId);

        // Retrieve the interaction for the current user
        $interaction = $video->interactions()->where('user_id', Auth::user()->id)->first();

        // Check if the interaction exists
        if (!$interaction) {
            return ResourceApi::sendResponse(404, __('messages.interaction_not_found'));
        }

        // If the interaction is a like, decrement the like count
        if ($interaction->type === 'like') {
            $video->decrement('likes'); // Decrease the like count
        }

        // Delete the interaction
        $interaction->delete();

        return ResourceApi::sendResponse(200, __('messages.interaction_deleted_successfully'), []);
    }

    private function extractHashtags($description)
    {
        preg_match_all('/#(\w+)/', $description, $matches);
        return $matches[1]; // تعيد قائمة بالهاشتاجات الموجودة في الوصف
    }

    public function getVideosByHashtag($hashtag)
    {
        // البحث عن الفيديوهات التي تحتوي على الهاشتاج المطلوب
        $videos = Video::whereHas('hashtags', function ($query) use ($hashtag) {
            $query->where('tag', $hashtag);
        })->paginate(10);

        // تنسيق الاستجابة
        $data = $videos->map(function ($video) {
            return [
                'id' => $video->id,
                'title' => $video->title,
                'url' => asset($video->video_path),
                'likes_count' => $video->interactions->count(),
                'views' => $video->views,
                'description' => $video->description,
                'created_at' => $video->created_at,
            ];
        });

        return ResourceApi::pagination($videos, $data);
    }

    public function getTopHashtagsWithVideos()
    {
        $user = Auth::user();

        // Get the most popular hashtags with the count of associated videos
        $hashtags = Hashtag::select('tag', DB::raw("count(*) as count"))
            ->groupBy("tag")
            ->orderBy('count', 'desc')
            ->take(2) // Limit the number of hashtags to retrieve
            ->get();

        $videoData = []; // To store videos associated with each hashtag

        foreach ($hashtags as $hashtag) {
            // Get videos related to this hashtag
            $videos = Video::with(['interactions', 'user'])
                ->whereHas("hashtags", function ($query) use ($hashtag) {
                    $query->where('tag', $hashtag->tag);
                })
                ->orderBy("views", 'desc')
                ->take(10)
                ->get();

            // Store the count of videos
            $videoData[$hashtag->tag]["count_videos"] = $hashtag->count;

            // Store video details
            $videoData[$hashtag->tag]["videos"] = $videos->map(function ($video) use ($user) {
                // Check if the current user has liked the video
                $hasLiked = $video->interactions->where('user_id', $user->id)->where('type', 'like')->isNotEmpty();

                // Check if the current user is following the video owner
                $isFollowing = $user->following()->where('user_id', $video->user->id)->exists();

                // Check if the current user has saved the video
                $isSaved = $user->savedVideos()->where('video_id', $video->id)->exists();

                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'url' => asset($video->video_path),
                    'likes_count' => $video->interactions->count(),
                    'views' => $video->views,
                    'description' => $video->description,
                    'has_liked' => $hasLiked,
                    'is_saved' => $isSaved,
                    'created_at' => $video->created_at->toDateTimeString(),
                    'user' => [
                        'id' => $video->user->id,
                        'name' => $video->user->name,
                        'username' => $video->user->username,
                        'profile_image' => $video->user->profile_image,
                        'is_following' => $isFollowing,
                    ],
                ];
            });
        }

        // Return the structured data with a success message
        return ResourceApi::sendResponse(200, __('messages.top_hashtags_retrieved_successfully'), $videoData);
    }

}

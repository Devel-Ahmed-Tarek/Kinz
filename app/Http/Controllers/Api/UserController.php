<?php

namespace App\Http\Controllers\Api;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\Follower;
use App\Models\ReportType;
use App\Models\User;
use App\Notifications\PusherNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class UserController extends Controller
{

    public function followUser(Request $request, $id)
    {
        $user = auth()->user();
        $userToFollow = User::findOrFail($id);

        // التأكد من أن المستخدم لا يحاول متابعة نفسه
        if ($user->id === $userToFollow->id) {
            return resourceApi::sendResponse(400, __('messages.You cannot follow yourself'));
        }

        // تحقق مما إذا كان المستخدم يتبع بالفعل
        if ($user->following()->where('user_id', $userToFollow->id)->exists()) {
            return resourceApi::sendResponse(400, __('messages.Already following this user'));
        }

        // إنشاء سجل متابعة جديد
        Follower::create([
            'user_id' => $userToFollow->id,
            'follower_id' => $user->id,
        ]);

        $data = [
            'type' => 'follower',
            'user' => $user,
        ];

        Notification::send($userToFollow, new PusherNotification($data));

        return resourceApi::sendResponse(200, __('messages.Followed successfully'));
    }

    public function unfollowUser(Request $request, $id)
    {
        $user = auth()->user();
        $userToUnfollow = User::findOrFail($id);

        // التأكد من أن المستخدم لا يحاول إلغاء متابعة نفسه
        if ($user->id === $userToUnfollow->id) {
            return resourceApi::sendResponse(400, __('messages.You cannot unfollow yourself'));
        }

        // تحقق مما إذا كان المستخدم يتبع هذا المستخدم
        $follower = Follower::where('user_id', $userToUnfollow->id)
            ->where('follower_id', $user->id)
            ->first();

        if (!$follower) {
            return resourceApi::sendResponse(400, __('messages.Not following this user'));
        }

        // حذف سجل المتابعة
        $follower->delete();

        return resourceApi::sendResponse(200, __('messages.Unfollowed successfully'));
    }

    public function getFollowing()
    {
        $user = auth()->user();

        // Retrieve the user IDs of the users the authenticated user is following
        $followingIds = $user->following()->pluck('user_id')->toArray();

        // Fetch the information of the following users
        $followingUsers = User::whereIn('id', $followingIds)->get();

        // Map the data to include the 'follow' status (always true since it's the following list)
        $followingUsersWithStatus = $followingUsers->map(function ($followingUser) {
            return [
                'id' => $followingUser->id,
                'name' => $followingUser->name,
                'userName' => $followingUser->username,
                'email' => $followingUser->email,
                'profile_image' => $followingUser->profile_image,
                'follow' => true, // Always true because this is the following list
            ];
        });

        // Return the response with the following users' data
        return resourceApi::sendResponse(200, __('messages.following_users_retrieved_successfully'), $followingUsersWithStatus);
    }
    public function getFollowers()
    {
        $user = auth()->user();

        // Retrieve the user IDs of the users following the authenticated user
        $followerIds = $user->followers()->pluck('follower_id')->toArray();

        // Fetch the information of the followers
        $followerUsers = User::whereIn('id', $followerIds)->get();

        // Map the data to include the 'follow' status (true if the authenticated user follows them back)
        $followerUsersWithStatus = $followerUsers->map(function ($followerUser) use ($user) {
            return [
                'id' => $followerUser->id,
                'name' => $followerUser->name,
                'userName' => $followerUser->username,
                'profile_image' => $followerUser->profile_image,
                'email' => $followerUser->email,
                'follow' => $user->following()->where('user_id', $followerUser->id)->exists(), // Check if the user follows back
            ];
        });

        // Return the response with the followers' data
        return resourceApi::sendResponse(200, __('messages.followers_retrieved_successfully'), $followerUsersWithStatus);
    }

    public function getUserFollowing($id)
    {
        $user = User::FindOrFail($id);

        // Retrieve the user IDs of the users the authenticated user is following
        $followingIds = $user->following()->pluck('user_id')->toArray();

        // Fetch the information of the following users
        $followingUsers = User::whereIn('id', $followingIds)->get();

        // Map the data to include the 'follow' status (always true since it's the following list)
        $followingUsersWithStatus = $followingUsers->map(function ($followingUser) {
            return [
                'id' => $followingUser->id,
                'name' => $followingUser->name,
                'userName' => $followingUser->username,
                'email' => $followingUser->email,
                'profile_image' => $followingUser->profile_image,
                'follow' => true, // Always true because this is the following list
            ];
        });

        // Return the response with the following users' data
        return resourceApi::sendResponse(200, __('messages.following_users_retrieved_successfully'), $followingUsersWithStatus);
    }

    public function getUserFollowers($id)
    {
        $user = User::FindOrFail($id);

        // Retrieve the user IDs of the users following the authenticated user
        $followerIds = $user->followers()->pluck('follower_id')->toArray();

        // Fetch the information of the followers
        $followerUsers = User::whereIn('id', $followerIds)->get();

        // Map the data to include the 'follow' status (true if the authenticated user follows them back)
        $followerUsersWithStatus = $followerUsers->map(function ($followerUser) use ($user) {
            return [
                'id' => $followerUser->id,
                'name' => $followerUser->name,
                'profile_image' => $followerUser->profile_image,
                'userName' => $followerUser->username,
                'email' => $followerUser->email,
                'follow' => $user->following()->where('user_id', $followerUser->id)->exists(), // Check if the user follows back
            ];
        });

        // Return the response with the followers' data
        return resourceApi::sendResponse(200, __('messages.followers_retrieved_successfully'), $followerUsersWithStatus);
    }

    public function ReportType()
    {
        // Retrieve paginated report types
        $data = ReportType::paginate(10); // Paginate with 10 items per page

        // Map the data to include decoded JSON fields
        $mappedData = $data->getCollection()->map(function ($reportType) {
            return [
                'id' => $reportType->id,
                'name' => json_decode($reportType->name, true), // Decode the name from JSON
                'type' => json_decode($reportType->type, true), // Decode the type from JSON
                'status' => $reportType->status,
            ];
        });

        // Return the paginated data
        return resourceApi::pagination($data, $mappedData);
    }

    public function searchUsers(Request $request)
    {
        // تحقق من وجود كلمة البحث
        $query = $request->input('query');
        if (!$query) {
            return resourceApi::sendResponse(400, __('messages.Search query is required'));
        }

        // البحث عن المستخدمين باستخدام name أو username
        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('username', 'LIKE', "%{$query}%")
            ->get();

        // إذا لم يتم العثور على نتائج
        if ($users->isEmpty()) {
            return resourceApi::sendResponse(404, __('messages.No users found'));
        }

        // تحضير البيانات للاستجابة
        $result = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'profile_image' => $user->profile_image,
            ];
        });

        // إرسال الاستجابة مع النتائج
        return resourceApi::sendResponse(200, __('messages.Users found successfully'), $result);
    }

    public function getFollowersOfFollowing()
    {
        // التحقق من أن المستخدم مسجل دخوله
        $user = auth()->user();

        if (!$user) {
            return resourceApi::sendResponse(401, __('messages.Unauthorized')); // أو أي رسالة تناسب
        }

        // جلب قائمة معرفات المستخدمين الذين يتابعهم المستخدم الحالي
        $followingIds = $user->following()->pluck('user_id')->toArray();

        // جلب 10 من المتابعين للمستخدمين الذين يتابعهم
        $followersOfFollowing = User::whereIn('id', function ($query) use ($followingIds) {
            $query->select('follower_id')
                ->from('followers')
                ->whereIn('user_id', $followingIds);
        })
            ->take(10) // أخذ 10 فقط
            ->get();

        // تنسيق البيانات مع الستوري
        $followersWithStatus = $followersOfFollowing->map(function ($follower) use ($user) {
            // جلب الستوري الخاصة بالمستخدم إذا كانت موجودة
            $story = $follower->stories()->latest()->first(); // جلب آخر ستوري

            return [
                'id' => $follower->id,
                'name' => $follower->name,
                'username' => $follower->username,
                'profile_image' => $follower->profile_image,
                'email' => $follower->email,
                'is_following' => $user->following()->where('user_id', $follower->id)->exists(), // التأكد من أن المستخدم الحالي يتابعهم
                'story' => $story ? [
                    'id' => $story->id,
                    'type' => $story->type,
                    'content' => $story->content,
                    'created_at' => $story->created_at,
                ] : null, // إرجاع الستوري إذا كانت موجودة
            ];
        });

        return resourceApi::sendResponse(200, __('messages.followers_of_following_retrieved_successfully'), $followersWithStatus);
    }

}

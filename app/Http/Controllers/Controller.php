<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use Illuminate\Http\Request;

abstract class Controller
{
    public function followUser(Request $request, $id)
    {
        $user = auth()->user();
        $userToFollow = User::findOrFail($id);

        // تحقق مما إذا كان المستخدم يتبع بالفعل
        if ($user->following()->where('user_id', $userToFollow->id)->exists()) {
            return response()->json(['message' => 'Already following this user'], 400);
        }

        // إنشاء سجل متابعة جديد
        Follower::create([
            'user_id' => $userToFollow->id,
            'follower_id' => $user->id,
        ]);

        return response()->json(['message' => 'Followed successfully'], 200);
    }

    public function unfollowUser(Request $request, $id)
    {
        $user = auth()->user();
        $userToUnfollow = User::findOrFail($id);

        // تحقق مما إذا كان المستخدم يتبع هذا المستخدم
        $follower = Follower::where('user_id', $userToUnfollow->id)
            ->where('follower_id', $user->id)
            ->first();

        if (!$follower) {
            return response()->json(['message' => 'Not following this user'], 400);
        }

        // حذف سجل المتابعة
        $follower->delete();

        return response()->json(['message' => 'Unfollowed successfully'], 200);
    }

    public function getFollowing()
    {
        $user = auth()->user();
        $following = $user->following()->with('user')->get();

        return response()->json([
            'message' => 'Following users retrieved successfully',
            'following' => $following->map(function ($follower) {
                return $follower->user;
            }),
        ], 200);
    }

    public function getFollowers()
    {
        $user = auth()->user();
        $followers = $user->followers()->with('follower')->get();

        return response()->json([
            'message' => 'Followers retrieved successfully',
            'followers' => $followers->map(function ($follower) {
                return $follower->follower;
            }),
        ], 200);
    }
}

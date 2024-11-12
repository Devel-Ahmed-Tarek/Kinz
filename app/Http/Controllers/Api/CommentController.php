<?php

namespace App\Http\Controllers\Api;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function addComment(Request $request, $videoId)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id', // Ensure parent comment exists
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        // Fetch the video or fail
        $video = Video::find($videoId);
        if (!$video) {
            return resourceApi::sendResponse(404, __('messages.Video not found'), []);
        }

        // Create the comment
        $comment = $video->comments()->create([
            'user_id' => auth()->id(), // Use authenticated user directly
            'content' => $request->input('content'),
            'parent_id' => $request->input('parent_id'), // Include parent_id if it's a reply
        ]);

        // Format the response data (including user details)
        $formattedComment = [
            'id' => $comment->id,
            'content' => $comment->content,
            'created_at' => $comment->created_at->toDateTimeString(),
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'username' => $comment->user->username,
                'profile_image' => $comment->user->profile_image,
            ],
            'parent_id' => $comment->parent_id,
        ];

        // Return the comment in the response
        return resourceApi::sendResponse(200, __('messages.Comment added successfully'), $formattedComment);
    }

    public function updateComment(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        // تأكد من أن المستخدم هو من قام بإنشاء التعليق
        if ($comment->user_id !== auth()->user()->id) {
            return resourceApi::sendResponse(403, __('messages.Unauthorized'));
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, __('messages.Validation errors'), $validator->messages()->all());
        }

        $comment->update([
            'content' => $request->input('content'),
        ]);

        return resourceApi::sendResponse(200, __('messages.Comment updated successfully'), $comment);
    }

    public function deleteComment($id)
    {
        $comment = Comment::findOrFail($id);

        // تأكد من أن المستخدم هو من قام بإنشاء التعليق
        if ($comment->user_id !== auth()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();
        return resourceApi::sendResponse(200, ['en' => 'Comment deleted successfully', 'ar' => 'تم  حذف التعليق  بنجاح']);

    }
    public function getMainComments($videoId)
    {
        // Find the video or fail if not found
        $video = Video::findOrFail($videoId);

        // Get main comments (where parent_id is null)
        $comments = $video->comments()->whereNull('parent_id')->with(['user', 'reactions'])->get();

        // Get the authenticated user
        $authUserId = auth()->id();

        // Map comments to include desired fields
        $formattedComments = $comments->map(function ($comment) use ($authUserId) {
            // Count the reactions
            $likeCount = $comment->reactions()->where('reaction', 'like')->count();

            // Check if the authenticated user has liked this comment
            $userLiked = $comment->reactions()->where('user_id', $authUserId)->where('reaction', 'like')->exists();

            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->toDateTimeString(),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'username' => $comment->user->username,
                    'profile_image' => $comment->user->profile_image,
                ],
                'reactions' => [
                    'likes' => $likeCount,
                    'user_liked' => $userLiked,
                ],
            ];
        });

        return response()->json([
            'status' => 'success',
            'main_comments' => $formattedComments,
        ], 200);
    }

    public function getChildComments($commentId)
    {
        // Find the main comment or fail if not found
        $mainComment = Comment::findOrFail($commentId);

        // Get child comments (where parent_id is the main comment's ID)
        $childComments = $mainComment->children()->with(['user', 'reactions'])->get();

        // Get the authenticated user
        $authUserId = auth()->id();

        // Map child comments to include desired fields
        $formattedChildComments = $childComments->map(function ($comment) use ($authUserId) {
            // Count the reactions
            $likeCount = $comment->reactions()->where('reaction', 'like')->count();

            // Check if the authenticated user has liked this comment
            $userLiked = $comment->reactions()->where('user_id', $authUserId)->where('reaction', 'like')->exists();

            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->toDateTimeString(),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'username' => $comment->user->username,
                    'profile_image' => $comment->user->profile_image,
                ],
                'reactions' => [
                    'likes' => $likeCount,
                    'user_liked' => $userLiked,
                ],
            ];
        });

        return response()->json([
            'status' => 'success',
            'child_comments' => $formattedChildComments,
        ], 200);
    }

    // إضافة تفاعل مع تعليق
    public function reactToComment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reaction' => 'required|in:like,dislike',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->messages()->all(),
            ], 422);
        }

        $comment = Comment::findOrFail($id);
        $user = auth()->user();

        // حذف التفاعل إذا كان موجودًا بالفعل
        $existingReaction = CommentReaction::where('comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingReaction) {
            $existingReaction->delete();
        }

        // إضافة تفاعل جديد
        CommentReaction::create([
            'comment_id' => $comment->id,
            'user_id' => $user->id,
            'reaction' => $request->input('reaction'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Reaction added successfully',
        ], 201);
    }

    // جلب التفاعلات على تعليق
    public function getCommentReactions($id)
    {
        $comment = Comment::findOrFail($id);
        $reactions = $comment->reactions()->get();

        return response()->json([
            'status' => 'success',
            'reactions' => $reactions,
        ], 200);
    }
}

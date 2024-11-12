<?php

use App\Http\Controllers\Api\admin\CommentReportController;
use App\Http\Controllers\Api\admin\MessageReportController;
use App\Http\Controllers\Api\admin\ReportVideoController;
use App\Http\Controllers\Api\admin\StoryController;
use App\Http\Controllers\Api\admin\UserReportController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BayGiftsController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PointsWalletController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\VotesWalletController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'language'], function () {

    Route::post('/register/{locale?}', [AuthController::class, 'register']);

    Route::post('otp/send/{locale?}', [AuthController::class, 'sendOtp']);
    Route::post('password/forgot/{locale?}', [AuthController::class, 'forgotPassword']);
    Route::post('/password/reset/{locale?}', [AuthController::class, 'reset']);

    Route::post('/user/verified/{locale?}', [AuthController::class, 'verified']);
    Route::middleware('guest:sanctum')->post('/login/{locale?}', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout/{locale?}', [AuthController::class, 'logout']);

    Route::post('otp/verify/{locale?}', [AuthController::class, 'verifyOtp']);
    Route::middleware('auth:sanctum')->get('/profile/{locale?}', [AuthController::class, 'profile']);
    Route::middleware('auth:sanctum')->get('/user/profile/{id}', [AuthController::class, 'profileUser']);
    Route::middleware('auth:sanctum')->post('/update-profile/{locale?}', [AuthController::class, 'update_profile']);
    Route::middleware('auth:sanctum')->post('/change-password/{locale?}', [AuthController::class, 'changePassword']);
    Route::post('/user/profile-image', [AuthController::class, 'updateProfileImage'])->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->post('/videos/upload/{locale?}', [VideoController::class, 'uploadVideo']);
    Route::middleware('auth:sanctum')->post('/videos/profile/{locale?}', [VideoController::class, 'index']);
    Route::middleware('auth:sanctum')->get('/get-videos-by-user/{id}/{locale?}', [VideoController::class, 'getVideosByUserId']);
    Route::middleware('auth:sanctum')->get('/videos/homePage/{locale?}', [VideoController::class, 'homePage']);
    Route::middleware('auth:sanctum')->post('/videos/{id}/view/{locale?}', [VideoController::class, 'incrementViews']);
    Route::middleware('auth:sanctum')->post('/videos/{id}/like/{locale?}', [VideoController::class, 'likeVideo']);
    Route::middleware('auth:sanctum')->get('/videos/{id}/remove/like/{locale?}', [VideoController::class, 'removeInteractionLikes']);
    Route::middleware('auth:sanctum')->post('/videos/{id}/report/{locale?}', [VideoController::class, 'reportVideo']);
    Route::middleware('auth:sanctum')->get('/videos/{id}/{locale?}', [VideoController::class, 'show']);
    Route::middleware('auth:sanctum')->post('/reportVideo/{id}/{locale?}', [VideoController::class, 'reportVideo']);
    Route::middleware('auth:sanctum')->get('/downloadVideo/{id}/{locale?}', [VideoController::class, 'downloadVideo']);
    Route::middleware('auth:sanctum')->get('/remove-to-saved-video/{videoid}/{locale?}', [VideoController::class, 'removeFromSavedVideos']);
    Route::middleware('auth:sanctum')->get('/add-to-saved-video/{videoid}/{locale?}', [VideoController::class, 'addToSavedVideos']);

    Route::middleware('auth:sanctum')->post('/users/{id}/follow/{locale?}', [UserController::class, 'followUser']);
    Route::middleware('auth:sanctum')->post('/users/searsh/{locale?}', [UserController::class, 'searchUsers']);
    Route::middleware('auth:sanctum')->post('/users/{id}/unfollow/{locale?}', [UserController::class, 'unfollowUser']);
    Route::middleware('auth:sanctum')->get('/users/following/{locale?}', [UserController::class, 'getFollowing']);
    Route::middleware('auth:sanctum')->get('/users/followers/{locale?}', [UserController::class, 'getFollowers']);
    Route::middleware('auth:sanctum')->get('/users/following/{id}/{locale?}', [UserController::class, 'getUserFollowing']);
    Route::middleware('auth:sanctum')->get('/users/followers/{id}/{locale?}', [UserController::class, 'getUserFollowers']);
    Route::middleware('auth:sanctum')->get('/report/type/{locale?}', [UserController::class, 'ReportType']);
    Route::middleware('auth:sanctum')->get('followers-of-following', [UserController::class, 'getFollowersOfFollowing']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/videos/{videoId}/comments/{locale?}', [CommentController::class, 'addComment']);
        Route::put('/comments/{id}/{locale?}', [CommentController::class, 'updateComment']);
        Route::delete('/comments/{id}/{locale?}', [CommentController::class, 'deleteComment']);
        Route::get('/videos/{videoId}/main/comments/{locale?}', [CommentController::class, 'getMainComments']);
        Route::get('/comment/{commentId}/child/comments/{locale?}', [CommentController::class, 'getChildComments']);
        Route::post('/comments/{id}/react/{locale?}', [CommentController::class, 'reactToComment']);
        Route::get('/comments/{id}/reactions/{locale?}', [CommentController::class, 'getCommentReactions']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('chat/{userId}/{locale?}', [ChatController::class, 'getOrCreateChat']);
        Route::get('messages/{chatId}/{locale?}', [ChatController::class, 'getMessages']);
        Route::post('messages/{chatId}/{locale?}', [ChatController::class, 'sendMessage']);
        Route::delete('messages/{messageId}/{locale?}', [ChatController::class, 'deleteMessage']);
        Route::get('search-chats/{locale?}', [ChatController::class, 'searchChats']);
        Route::get('user-chats/{locale?}', [ChatController::class, 'getUserChats']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('report-video/{locale?}', [ReportVideoController::class, 'store']);
        Route::post('/comments/report/{locale?}', [CommentReportController::class, 'store']);
        Route::post('user-reports/{locale?}', [UserReportController::class, 'store']);
        Route::post('message-reports/{locale?}', [MessageReportController::class, 'store']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/stories', [StoryController::class, 'store']);
        Route::get('/stories', [StoryController::class, 'getFollowingUsersWithStories']);
        Route::get('/my-stories', [StoryController::class, 'GeMyStories']);
        Route::post('/stories/{id}/like', [StoryController::class, 'like']);
        Route::post('/stories/{id}/reply', [StoryController::class, 'reply']);
        Route::post('/stories/{id}/report', [StoryController::class, 'report']);
        Route::delete('/stories/{id}', [StoryController::class, 'delete']);
    });

// Routes لإدارة محفظة النقاط
    Route::middleware('auth:sanctum')->group(function () {
        // عرض رصيد محفظة النقاط
        Route::get('/points', [PointsWalletController::class, 'index']);
        Route::get('/points-wallet', [PointsWalletController::class, 'showPointsWallet']);

        // إضافة نقاط إلى محفظة النقاط
        Route::post('/points-wallet/add', [PointsWalletController::class, 'addPoints']);

        // عرض رصيد محفظة التصويطات
        Route::get('/votes-wallet', [VotesWalletController::class, 'showVotesWallet']);
        Route::get('/votes', [VotesWalletController::class, 'index']);

        // إضافة تصويطات إلى محفظة التصويطات
        Route::post('/votes-wallet/add', [VotesWalletController::class, 'addVotes']);

        Route::get('/gifts', [BayGiftsController::class, 'index']);
        Route::post('/buy-gift', [BayGiftsController::class, 'buyGift'])->middleware('auth:sanctum');

        // شراء النقاط أو التصويطات
        Route::post('/wallet/purchase', [PointsWalletController::class, 'purchasePoints']);

        // استلام إشعار الدفع الناجح
        Route::post('/wallet/payment-callback', [PointsWalletController::class, 'paymentCallback'])->name('myfatoorah.callback');
    });
    ////  Hashtags
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/hashtags/top', [VideoController::class, 'getTopHashtagsWithVideos']);

    });

});

require base_path('routes/admin.php');

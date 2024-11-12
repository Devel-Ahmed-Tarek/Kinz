<?php

use App\Http\Controllers\Api\admin\AdminUserController;
use App\Http\Controllers\Api\admin\AuthAdminController;
use App\Http\Controllers\Api\admin\CommentReportController;
use App\Http\Controllers\Api\admin\GiftsController;
use App\Http\Controllers\Api\admin\MessageReportController;
use App\Http\Controllers\Api\admin\PointController;
use App\Http\Controllers\Api\admin\ReportTypeController;
use App\Http\Controllers\Api\admin\ReportVideoController;
use App\Http\Controllers\Api\admin\StoryReportController;
use App\Http\Controllers\Api\admin\UserReportController;
use App\Http\Controllers\Api\admin\VoiceController;
use App\Http\Controllers\Api\admin\VoteController;
use Illuminate\Support\Facades\Route;

//   Admin
Route::prefix('admin')->controller(AuthAdminController::class)->group(function () {
    Route::post('/login', 'login');
});

Route::middleware(['auth:sanctum', 'typeUser:admin'])->prefix('admin')->group(function () {

    Route::get('user', [AdminUserController::class, 'index']);
    Route::delete('user-delete/{id}', [AdminUserController::class, 'delete']);
    Route::delete('user-delete-ban/{user_id}', [AdminUserController::class, 'deleteBan']);
    Route::put('block-user', [AdminUserController::class, 'banUser']);

});

Route::middleware(['auth:sanctum', 'typeUser:admin'])->prefix('admin')->group(function () {

    Route::post('add-reportType', [ReportTypeController::class, 'add']);
    Route::post('update-reportType/{id}', [ReportTypeController::class, 'update']);
    Route::post('updateStatus-reportType', [ReportTypeController::class, 'updateStatus']);
    Route::get('reportType', [ReportTypeController::class, 'index']);
    Route::get('reportType/{id}', [ReportTypeController::class, 'show']);
    Route::delete('delete-reportType/{id}', [ReportTypeController::class, 'delete']);

});

Route::middleware(['auth:sanctum', 'typeUser:admin'])->prefix('admin')->group(function () {

    Route::get('/points', [PointController::class, 'index']);
    Route::post('/points', [PointController::class, 'store']);
    Route::get('/points/{id}', [PointController::class, 'show']);
    Route::post('/points/{id}', [PointController::class, 'update']);
    Route::delete('/points/{id}', [PointController::class, 'destroy']);
    Route::post('/points/{id}/status', [PointController::class, 'updateStatus']);

});
Route::middleware(['auth:sanctum', 'typeUser:admin'])->prefix('admin')->group(function () {

    Route::get('/votes', [VoteController::class, 'index']);
    Route::post('/votes', [VoteController::class, 'store']);
    Route::get('/votes/{id}', [VoteController::class, 'show']);
    Route::post('/votes/{id}', [VoteController::class, 'update']);
    Route::delete('/votes/{id}', [VoteController::class, 'destroy']);
    Route::post('/votes/{id}/status', [VoteController::class, 'updateStatus']);

});

Route::middleware(['auth:sanctum', 'typeUser:admin'])->prefix('admin')->group(function () {

    Route::get('/gifts', [GiftsController::class, 'index']);
    Route::post('/gifts', [GiftsController::class, 'store']); // إضافة نقاط شراء جديدة
    Route::get('/gifts/{id}', [GiftsController::class, 'show']); // عرض نقاط شراء بناءً على الـ ID
    Route::post('/gifts/{id}', [GiftsController::class, 'update']); // تعديل نقاط شراء بناءً على الـ ID
    Route::delete('/gifts/{id}', [GiftsController::class, 'destroy']); // حذف نقاط شراء بناءً على الـ ID
    Route::post('/gifts/{id}/status', [GiftsController::class, 'updateStatus']); // تحديث حالة نقطة شراء بناءً على الـ ID

});

Route::middleware(['auth:sanctum', 'typeUser:admin'])->prefix('admin')->group(function () {

    Route::get('report-videos', [ReportVideoController::class, 'index']);
    Route::post('report-videos', [ReportVideoController::class, 'store']);
    Route::post('report-videos/{id}', [ReportVideoController::class, 'update']);
    Route::delete('report-videos/{id}', [ReportVideoController::class, 'destroy']);

});

Route::middleware(['auth:sanctum', 'typeUser:admin'])->prefix('admin')->group(function () {

    Route::post('/comments/report', [CommentReportController::class, 'store']);
    Route::get('/comments/reports', [CommentReportController::class, 'index']);
    Route::post('/comments/reports/{id}', [CommentReportController::class, 'update']);
    Route::delete('/comments/reports/{id}', [CommentReportController::class, 'destroy']);

});

Route::middleware(['auth:sanctum', 'typeUser:admin'])->prefix('admin/user-reports')->group(function () {

    Route::get('/', [UserReportController::class, 'index']);
    Route::get('/{id}', [UserReportController::class, 'show']);
    Route::post('/{id}', [UserReportController::class, 'update']);
    Route::delete('/{id}', [UserReportController::class, 'destroy']);

});

Route::middleware(['auth:sanctum', 'typeUser:admin'])->prefix('admin/')->group(function () {

    Route::get('voices', [VoiceController::class, 'index']);
    Route::post('voices', [VoiceController::class, 'store']);
    Route::get('voices/{id}', [VoiceController::class, 'show']);
    Route::post('voices/{id}', [VoiceController::class, 'update']);
    Route::delete('voices/{id}', [VoiceController::class, 'destroy']);

});

Route::middleware(['auth:sanctum', 'typeUser:admin'])->prefix('admin/message-reports')->group(function () {

    Route::get('/', [MessageReportController::class, 'index']);
    Route::post('/{id}', [MessageReportController::class, 'update']);
    Route::delete('/{id}', [MessageReportController::class, 'destroy']);

});

Route::middleware(['auth:sanctum'])->prefix('admin/stories')->group(function () {
    // Route to get all reports for a specific story
    Route::get('/reports', [StoryReportController::class, 'index']);

    // Route to delete a specific report (if authorized)
    Route::delete('report/{id}', [StoryReportController::class, 'destroy']);

});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AvailabilityFrameController;

// ==========================
// Appointment Routes
// ==========================
Route::prefix('appointments')->middleware('auth')->group(function () {
    // 创建预约
    Route::post('/', [AppointmentController::class, 'store']);

    // 查看单个预约
    Route::get('/{id}', [AppointmentController::class, 'show']);

    // 更新预约
    Route::put('/{id}', [AppointmentController::class, 'update']);

    // 取消预约
    Route::patch('/{id}/cancel', [AppointmentController::class, 'cancel']);
});

// ==========================
// Availability Frame Routes
// ==========================
Route::prefix('availability-frames')->middleware('auth')->group(function () {
    // 获取所有 frame（管理员用）
    Route::get('/', [AvailabilityFrameController::class, 'index']);

    // 根据 staff 获取 frame（员工用）
    Route::get('/staff/{staffId}', [AvailabilityFrameController::class, 'getByStaff']);

    // 获取单个 frame 详情
    Route::get('/{id}', [AvailabilityFrameController::class, 'show']);

    // 创建新的 frame
    Route::post('/', [AvailabilityFrameController::class, 'store']);

    // 更新 frame
    Route::put('/{id}', [AvailabilityFrameController::class, 'update']);

    // 删除单个 frame
    Route::delete('/{id}', [AvailabilityFrameController::class, 'destroy']);

    // 批量删除同组 recurring frame
    Route::delete('/repeat-group/{repeatGroupId}', [AvailabilityFrameController::class, 'deleteByRepeatGroup']);
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AvailabilityFrameController;
use App\Http\Controllers\AvailabilitySlotController;
use App\Http\Controllers\PublicBookingController;

// ==========================
// Appointment Routes
// ==========================
Route::prefix('appointments')->middleware('auth')->group(function () {
    // Get appointments by staff ID
    Route::get('/staff/{staffId}', [AppointmentController::class, 'getByStaff']);

    // 创建预约
    Route::post('/', [AppointmentController::class, 'store']);

    // 查看单个预约
    Route::get('/{appointment}', [AppointmentController::class, 'show']);

    // 更新预约
    Route::put('/{appointment}', [AppointmentController::class, 'update']);

    // Delete appointment
    Route::delete('/{appointment}', [AppointmentController::class, 'destroy']);

    // 取消预约
    Route::patch('/{appointment}/cancel', [AppointmentController::class, 'cancel']);
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

    // Move frame (drag-and-drop with cascading slots)
    Route::patch('/{id}/move', [AvailabilityFrameController::class, 'move']);

    // 删除单个 frame
    Route::delete('/{id}', [AvailabilityFrameController::class, 'destroy']);

    // 批量删除同组 recurring frame
    Route::delete('/repeat-group/{repeatGroupId}', [AvailabilityFrameController::class, 'deleteByRepeatGroup']);
});

// ==========================
// Availability Slot Routes
// ==========================
Route::prefix('availability-slots')->middleware('auth')->group(function () {
    // Get all slots
    Route::get('/', [AvailabilitySlotController::class, 'index']);

    // Get single slot
    Route::get('/{id}', [AvailabilitySlotController::class, 'show']);

    // Create new slot
    Route::post('/', [AvailabilitySlotController::class, 'store']);

    // Update slot
    Route::put('/{id}', [AvailabilitySlotController::class, 'update']);

    // Delete slot
    Route::delete('/{id}', [AvailabilitySlotController::class, 'destroy']);
});

// ==========================
// Public Booking Routes (No Auth Required)
// ==========================
Route::prefix('public')->group(function () {
    // Get staff availability for booking page
    Route::get('/staff/{staffId}/availability', [PublicBookingController::class, 'getStaffAvailability']);

    // Create appointment from public booking page
    Route::post('/appointments', [PublicBookingController::class, 'store']);
});

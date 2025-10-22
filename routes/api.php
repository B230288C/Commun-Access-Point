<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

Route::prefix('appointments')->group(function () {
    // 创建预约
    Route::post('/', [AppointmentController::class, 'store']);

    // 查看单个预约
    Route::get('/{id}', [AppointmentController::class, 'show']);

    // 更新预约
    Route::put('/{id}', [AppointmentController::class, 'update']);

    // 取消预约
    Route::patch('/{id}/cancel', [AppointmentController::class, 'cancel']);
});

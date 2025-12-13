<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicBookingController;

Route::get('/', function () {
    return view('index');
});

// ==========================
// Authentication Routes
// ==========================
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth');

// ==========================
// Appointment CRUD Routes
// ==========================
Route::resource('appointments', AppointmentController::class)->middleware('auth');

// ==========================
// Public Booking Page (No Auth Required)
// ==========================
Route::get('/book/{staffId}', [PublicBookingController::class, 'show'])->name('booking.show');

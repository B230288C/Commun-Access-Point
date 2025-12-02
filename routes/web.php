<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

Route::get('/', function () {
    return view('index');
});

// Appointment CRUD routes
Route::resource('appointments', AppointmentController::class);

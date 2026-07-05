<?php

use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\HrDashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/attendance/check-in', [AttendanceApiController::class, 'checkIn']);
Route::post('/attendance/check-out', [AttendanceApiController::class, 'checkOut']);
Route::get('/attendance/history', [AttendanceApiController::class, 'history']);
Route::get('/hr/dashboard-summary', [HrDashboardController::class, 'summary']);

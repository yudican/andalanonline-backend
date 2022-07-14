<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CabangController;
use App\Http\Controllers\Api\DivisiController;
use App\Http\Controllers\Api\ShiftController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// login controler
//prefix auth
Route::group(['prefix' => 'auth'], function () {
    Route::post('request-otp', [AuthController::class, 'requestOtp']);
    Route::post('validate-otp', [AuthController::class, 'validateOtp']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    // 
    Route::get('cabang/all', [CabangController::class, 'index']);
    Route::get('divisi/all', [DivisiController::class, 'index']);
    Route::get('divisi/all', [DivisiController::class, 'index']);
});


Route::group(['prefix' => 'attendance', 'middleware' => 'auth:sanctum'], function () {
    Route::get('shift/all', [ShiftController::class, 'index']);

    Route::post('save', [AttendanceController::class, 'saveAttendance']);
    Route::post('history', [AttendanceController::class, 'attendanceHistory']);
    Route::post('report', [AttendanceController::class, 'reportAttendance']);

    // Attendance Request
    Route::post('request', [AttendanceController::class, 'attendanceRequest']);
    Route::get('request', [AttendanceController::class, 'getAttendanceRequest']);
});

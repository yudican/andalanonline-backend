<?php

use App\Http\Controllers\AuthController;
use App\Http\Livewire\CrudGenerator;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\Settings\Menu;
use App\Http\Livewire\UserManagement\Permission;
use App\Http\Livewire\UserManagement\PermissionRole;
use App\Http\Livewire\UserManagement\Role;
use App\Http\Livewire\UserManagement\User;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Master\CabangController;
use App\Http\Livewire\Master\DivisiController;
use App\Http\Livewire\Master\ShiftController;
use App\Http\Livewire\ScheduleShiftController;
use App\Http\Livewire\Attendance\AttendanceController;
use App\Http\Livewire\Attendance\AttendanceRequestController;
use App\Http\Livewire\Member\UserProfileController;
// [route_import_path]

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('login');
});


Route::post('login', [AuthController::class, 'login'])->name('admin.login');
Route::group(['middleware' => ['auth:sanctum', 'verified', 'user.authorization']], function () {
    // Crud Generator Route
    Route::get('/crud-generator', CrudGenerator::class)->name('crud.generator');

    // user management
    Route::get('/permission', Permission::class)->name('permission');
    Route::get('/permission-role/{role_id}', PermissionRole::class)->name('permission.role');
    Route::get('/role', Role::class)->name('role');
    Route::get('/user', User::class)->name('user');
    Route::get('/menu', Menu::class)->name('menu');

    // App Route
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Master data

    Route::get('/cabang', CabangController::class)->name('cabang');
    Route::get('/divisi', DivisiController::class)->name('divisi');
    Route::get('/shift', ShiftController::class)->name('shift');
    Route::get('/schedule-shifts', ScheduleShiftController::class)->name('schedule-shifts');
    Route::get('/attendances', AttendanceController::class)->name('attendances');
    Route::get('/attendance-request', AttendanceRequestController::class)->name('attendance-request');
    Route::get('/user-profiles', UserProfileController::class)->name('user-profiles');
    // [route_path]

});

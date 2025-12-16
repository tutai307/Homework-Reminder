<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes
Route::get('login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('classes', \App\Http\Controllers\Admin\ClassController::class);
    Route::resource('subjects', \App\Http\Controllers\Admin\SubjectController::class);
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
});

Route::prefix('teacher')->name('teacher.')->middleware('auth')->group(function () {
    Route::get('timetables', [\App\Http\Controllers\Teacher\TimetableController::class, 'index'])->name('timetables.index');
    Route::get('timetables/{class}/create', [\App\Http\Controllers\Teacher\TimetableController::class, 'create'])->name('timetables.create');
    Route::post('timetables/{class}', [\App\Http\Controllers\Teacher\TimetableController::class, 'store'])->name('timetables.store');
    
    Route::get('daily-homework', [\App\Http\Controllers\Teacher\DailyHomeworkController::class, 'index'])->name('daily-homework.index');
    Route::get('daily-homework/list', [\App\Http\Controllers\Teacher\DailyHomeworkController::class, 'list'])->name('daily-homework.list');
    Route::get('daily-homework/get', [\App\Http\Controllers\Teacher\DailyHomeworkController::class, 'getHomework'])->name('daily-homework.get');
    Route::get('daily-homework/zalo-message', [\App\Http\Controllers\Teacher\DailyHomeworkController::class, 'getZaloMessage'])->name('daily-homework.zalo-message');
    Route::get('daily-homework/create', [\App\Http\Controllers\Teacher\DailyHomeworkController::class, 'create'])->name('daily-homework.create');
    Route::post('daily-homework', [\App\Http\Controllers\Teacher\DailyHomeworkController::class, 'store'])->name('daily-homework.store');
    Route::get('daily-homework/{homework}/edit', [\App\Http\Controllers\Teacher\DailyHomeworkController::class, 'edit'])->name('daily-homework.edit');
    Route::put('daily-homework/{homework}', [\App\Http\Controllers\Teacher\DailyHomeworkController::class, 'update'])->name('daily-homework.update');
    Route::delete('daily-homework/{homework}', [\App\Http\Controllers\Teacher\DailyHomeworkController::class, 'destroy'])->name('daily-homework.destroy');
    
    Route::get('class-monitor', [\App\Http\Controllers\Teacher\ClassMonitorController::class, 'index'])->name('class-monitor.index');
    Route::get('class-monitor/create', [\App\Http\Controllers\Teacher\ClassMonitorController::class, 'create'])->name('class-monitor.create');
    Route::post('class-monitor', [\App\Http\Controllers\Teacher\ClassMonitorController::class, 'store'])->name('class-monitor.store');
    Route::delete('class-monitor/{classMonitor}', [\App\Http\Controllers\Teacher\ClassMonitorController::class, 'destroy'])->name('class-monitor.destroy');
});

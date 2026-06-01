<?php

use Illuminate\Support\Facades\Route;
use Modules\System\Http\Controllers\AuthController;
use Modules\System\Http\Controllers\DashboardController;
use Modules\System\Http\Controllers\UserController;
use Modules\System\Http\Controllers\RoleController;
use Modules\System\Http\Controllers\StoreController;
use Modules\System\Http\Controllers\ModuleController;

/*
|--------------------------------------------------------------------------
| Auth Routes (Guest only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User management
    Route::resource('users', UserController::class);

    // Role management
    Route::resource('roles', RoleController::class);

    // Store management
    Route::resource('stores', StoreController::class);

    // Module toggle
    Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::post('/modules/{module}/toggle', [ModuleController::class, 'toggle'])->name('modules.toggle');
});

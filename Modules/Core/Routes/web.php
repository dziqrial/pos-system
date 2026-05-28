<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\OutletController;

Route::middleware('auth')->group(function () {
    // Outlet management
    Route::resource('outlets', OutletController::class);
});

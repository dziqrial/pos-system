<?php

use Illuminate\Support\Facades\Route;
use Modules\Loyalty\Http\Controllers\CustomerController;
use Modules\Loyalty\Http\Controllers\VoucherController;

Route::middleware(['auth', 'module:loyalty'])->group(function () {
    Route::resource('customers', CustomerController::class);
    Route::resource('vouchers', VoucherController::class);
});

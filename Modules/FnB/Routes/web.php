<?php

use Illuminate\Support\Facades\Route;
use Modules\FnB\Http\Controllers\KitchenController;
use Modules\FnB\Http\Controllers\TableController;

Route::middleware(['auth', 'module:fnb'])->group(function () {
    Route::resource('tables', TableController::class);
    Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
    Route::post('/kitchen/{kitchenOrder}/items/{kitchenItem}/status', [KitchenController::class, 'updateItemStatus'])
        ->name('kitchen.item.status');
    Route::post('/kitchen/{kitchenOrder}/status', [KitchenController::class, 'updateOrderStatus'])
        ->name('kitchen.order.status');
});

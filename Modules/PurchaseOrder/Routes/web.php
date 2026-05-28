<?php

use Illuminate\Support\Facades\Route;
use Modules\PurchaseOrder\Http\Controllers\PurchaseOrderController;

Route::middleware(['auth', 'module:purchase_order'])->group(function () {
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('/purchase-orders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit'])
        ->name('purchase-orders.submit');
    Route::get('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receiveForm'])
        ->name('purchase-orders.receive-form');
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
        ->name('purchase-orders.receive');
    Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
        ->name('purchase-orders.cancel');
});

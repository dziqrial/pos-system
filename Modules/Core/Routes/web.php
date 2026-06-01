<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\CashierController;
use Modules\Core\Http\Controllers\CategoryController;
use Modules\Core\Http\Controllers\InventoryController;
use Modules\Core\Http\Controllers\OutletController;
use Modules\Core\Http\Controllers\ProductController;
use Modules\Core\Http\Controllers\RackController;
use Modules\Core\Http\Controllers\ReportController;
use Modules\Core\Http\Controllers\ShiftController;
use Modules\Core\Http\Controllers\SupplierController;
use Modules\Core\Http\Controllers\TransactionController;

Route::middleware('auth')->group(function () {

    // --- Outlets ---
    Route::resource('outlets', OutletController::class);
    Route::post('/outlet/switch', [CashierController::class, 'setOutlet'])->name('outlet.switch');

    // --- Kasir / POS ---
    Route::get('/kasir', [CashierController::class, 'index'])->name('kasir.index');
    Route::get('/kasir/search-product', [CashierController::class, 'searchProduct'])->name('kasir.search');
    Route::post('/kasir/checkout', [CashierController::class, 'checkout'])->name('kasir.checkout');

    // --- Shifts ---
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
    Route::post('/shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');

    // --- Categories ---
    Route::resource('categories', CategoryController::class);

    // --- Products & Variants ---
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
    Route::put('/products/{product}/variants/{variant}', [ProductController::class, 'updateVariant'])->name('products.variants.update');
    Route::delete('/products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');

    // --- Inventory ---
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{inventory}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::post('/inventory/{inventory}/adjust', [InventoryController::class, 'update'])->name('inventory.update');

    // --- Racks & Sub-racks ---
    Route::resource('racks', RackController::class);
    Route::post('/racks/{rack}/sub-racks', [RackController::class, 'storeSubRack'])->name('racks.sub-racks.store');
    Route::delete('/racks/{rack}/sub-racks/{subRack}', [RackController::class, 'destroySubRack'])->name('racks.sub-racks.destroy');

    // --- Suppliers ---
    Route::resource('suppliers', SupplierController::class);

    // --- Transactions ---
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{transaction}/void', [TransactionController::class, 'void'])->name('transactions.void');

    // --- Reports ---
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

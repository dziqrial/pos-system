<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Http\Controllers\AccountController;
use Modules\Accounting\Http\Controllers\JournalController;

Route::middleware(['auth', 'module:accounting'])->group(function () {
    Route::resource('accounts', AccountController::class);
    Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
    Route::get('/journal/create', [JournalController::class, 'create'])->name('journal.create');
    Route::post('/journal', [JournalController::class, 'store'])->name('journal.store');
    Route::get('/journal/{journalEntry}', [JournalController::class, 'show'])->name('journal.show');
});

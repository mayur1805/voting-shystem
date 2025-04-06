<?php

use Illuminate\Support\Facades\Route;


use App\AdminModule\Http\Controllers\AdminController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('election.data');
    Route::get('/election-report', [AdminController::class, 'report'])->name('election.report');
});

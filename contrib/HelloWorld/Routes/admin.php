<?php

use App\Modules\HelloWorld\Controllers\HelloWorldController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('hello-world', [HelloWorldController::class, 'index'])->name('hello-world.index');
});

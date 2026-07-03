<?php

use App\Modules\DemoNotes\Controllers\DemoNoteController;
use App\Modules\DemoNotes\Controllers\DemoTagController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('demo-notes', DemoNoteController::class)->except(['show']);
    Route::resource('demo-tags', DemoTagController::class)->except(['show']);
});

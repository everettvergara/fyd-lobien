<?php

use App\Http\Controllers\Public\ContentController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::post('/search', [SearchController::class, 'store'])->name('search.submit');
Route::get('/{slug}', [ContentController::class, 'show'])->name('content.show')->where('slug', '[a-z0-9\-]+');

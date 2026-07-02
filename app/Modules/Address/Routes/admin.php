<?php

use App\Modules\Address\Controllers\CityController;
use App\Modules\Address\Controllers\ProvinceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('provinces', [ProvinceController::class, 'index'])->name('provinces.index');
    Route::get('provinces/create', [ProvinceController::class, 'create'])->name('provinces.create');
    Route::post('provinces', [ProvinceController::class, 'store'])->name('provinces.store');
    Route::get('provinces/{province}', [ProvinceController::class, 'show'])->name('provinces.show');
    Route::get('provinces/{province}/edit', [ProvinceController::class, 'edit'])->name('provinces.edit');
    Route::put('provinces/{province}', [ProvinceController::class, 'update'])->name('provinces.update');
    Route::delete('provinces/{province}', [ProvinceController::class, 'destroy'])->name('provinces.destroy');

    Route::get('cities/by-province/{province}', [CityController::class, 'byProvince'])->name('cities.by-province');
    Route::get('cities', [CityController::class, 'index'])->name('cities.index');
    Route::get('cities/create', [CityController::class, 'create'])->name('cities.create');
    Route::post('cities', [CityController::class, 'store'])->name('cities.store');
    Route::get('cities/{city}', [CityController::class, 'show'])->name('cities.show');
    Route::get('cities/{city}/edit', [CityController::class, 'edit'])->name('cities.edit');
    Route::put('cities/{city}', [CityController::class, 'update'])->name('cities.update');
    Route::delete('cities/{city}', [CityController::class, 'destroy'])->name('cities.destroy');
});

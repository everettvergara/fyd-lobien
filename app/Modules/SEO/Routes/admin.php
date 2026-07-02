<?php

use App\Modules\SEO\Controllers\SeoReportController;
use App\Modules\SEO\Controllers\SitemapSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('seo/report', [SeoReportController::class, 'index'])->name('seo.report.index');
    Route::get('seo/sitemap', [SitemapSettingsController::class, 'index'])->name('seo.sitemap.index');
    Route::put('seo/sitemap', [SitemapSettingsController::class, 'update'])->name('seo.sitemap.update');
});

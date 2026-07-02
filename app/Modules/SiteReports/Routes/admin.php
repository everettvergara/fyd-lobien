<?php

use App\Modules\SiteReports\Controllers\BlockedIpController;
use App\Modules\SiteReports\Controllers\IpReportController;
use App\Modules\SiteReports\Controllers\PageReportController;
use App\Modules\SiteReports\Controllers\ReferrerReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('site-reports/pages', [PageReportController::class, 'index'])->name('site-reports.pages.index');
    Route::get('site-reports/ips', [IpReportController::class, 'index'])->name('site-reports.ips.index');
    Route::get('site-reports/referrers', [ReferrerReportController::class, 'index'])->name('site-reports.referrers.index');

    Route::post('site-reports/blocked-ips/{ip}', [BlockedIpController::class, 'store'])
        ->where('ip', '[0-9a-fA-F:\.]+')
        ->name('site-reports.blocked-ips.store');
    Route::delete('site-reports/blocked-ips/{blockedIp}', [BlockedIpController::class, 'destroy'])
        ->name('site-reports.blocked-ips.destroy');
});

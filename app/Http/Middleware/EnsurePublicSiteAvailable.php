<?php

namespace App\Http\Middleware;

use App\Services\SiteMaintenanceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublicSiteAvailable
{
    public function __construct(
        protected SiteMaintenanceService $maintenance,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $adminPrefix = trim(config('fyd.admin.prefix', 'admin'), '/');

        if ($request->is($adminPrefix, $adminPrefix.'/*')) {
            return $next($request);
        }

        if ($request->is('up')) {
            return $next($request);
        }

        if (! $this->maintenance->enabled()) {
            return $next($request);
        }

        $maintenancePath = ltrim(parse_url($this->maintenance->pageUrl(), PHP_URL_PATH) ?: '', '/');

        if ($maintenancePath !== '' && $request->is($maintenancePath)) {
            return $next($request);
        }

        return redirect()->to($this->maintenance->pageUrl());
    }
}

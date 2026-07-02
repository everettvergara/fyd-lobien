<?php

namespace App\Http\Middleware;

use App\Modules\SiteReports\Services\BlockedIpService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectBlockedIp
{
    public function __construct(
        protected BlockedIpService $blockedIps,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $adminPrefix = trim(config('fyd.admin.prefix', 'admin'), '/');

        if ($request->is($adminPrefix, $adminPrefix.'/*', 'up')) {
            return $next($request);
        }

        $ipAddress = (string) $request->ip();

        if ($ipAddress !== '' && $this->blockedIps->isBlocked($ipAddress)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}

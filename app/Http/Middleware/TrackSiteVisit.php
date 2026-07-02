<?php

namespace App\Http\Middleware;

use App\Modules\SiteReports\Services\BlockedIpService;
use App\Modules\SiteReports\Services\SiteVisitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackSiteVisit
{
    public function __construct(
        protected SiteVisitService $visits,
        protected BlockedIpService $blockedIps,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response->isSuccessful() || ! $this->visits->shouldTrack($request)) {
            return $response;
        }

        $ipAddress = (string) $request->ip();

        if ($ipAddress !== '' && ! $this->blockedIps->isBlocked($ipAddress)) {
            $this->visits->record($request);
        }

        return $response;
    }
}

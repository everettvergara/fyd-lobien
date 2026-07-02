<?php

namespace App\Modules\SiteReports\Services;

use App\Modules\SiteReports\Models\BlockedIp;
use App\Modules\SiteReports\Models\SiteVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SiteVisitService
{
    protected const BOT_PATTERNS = [
        'bot',
        'crawl',
        'spider',
        'slurp',
        'mediapartners',
        'facebookexternalhit',
        'bingpreview',
        'pingdom',
        'monitor',
        'headless',
    ];

    public function shouldTrack(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        $adminPrefix = trim(config('fyd.admin.prefix', 'admin'), '/');

        if ($request->is($adminPrefix, $adminPrefix.'/*', 'up')) {
            return false;
        }

        if ($this->isBot($request->userAgent())) {
            return false;
        }

        return true;
    }

    public function record(Request $request): void
    {
        if (! $this->shouldTrack($request)) {
            return;
        }

        $referer = $request->headers->get('referer');
        $refererHost = $this->resolveRefererHost($referer, $request);

        SiteVisit::create([
            'path' => '/'.ltrim($request->path(), '/'),
            'route_name' => $request->route()?->getName(),
            'ip_address' => (string) $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $referer,
            'referer_host' => $refererHost,
            'visited_at' => now(),
        ]);
    }

    public function resolveRefererHost(?string $referer, Request $request): ?string
    {
        if (! $referer) {
            return null;
        }

        $host = parse_url($referer, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        $host = strtolower($host);
        $appHost = strtolower($request->getHost());

        if ($host === $appHost) {
            return null;
        }

        return $host;
    }

    public function isBot(?string $userAgent): bool
    {
        if (! $userAgent) {
            return false;
        }

        $normalized = strtolower($userAgent);

        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return true;
            }
        }

        return false;
    }

    public function sinceFromPeriod(?string $period): Carbon
    {
        return match ($period) {
            '24h' => now()->subDay(),
            default => now()->subDays(7),
        };
    }

    public function pruneOlderThan(Carbon $cutoff): int
    {
        return SiteVisit::query()
            ->where('visited_at', '<', $cutoff)
            ->delete();
    }
}

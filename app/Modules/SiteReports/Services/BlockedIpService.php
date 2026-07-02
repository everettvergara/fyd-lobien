<?php

namespace App\Modules\SiteReports\Services;

use App\Modules\SiteReports\Models\BlockedIp;
use Illuminate\Support\Facades\Cache;

class BlockedIpService
{
    protected const CACHE_KEY = 'site_reports.blocked_ips';

    protected const CACHE_TTL = 60;

    public function isBlocked(string $ipAddress): bool
    {
        return in_array($ipAddress, $this->blockedAddresses(), true);
    }

    public function blockedAddresses(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return BlockedIp::query()
                ->pluck('ip_address')
                ->all();
        });
    }

    public function block(string $ipAddress, int $blockedBy, ?string $reason = null): BlockedIp
    {
        $blocked = BlockedIp::query()->updateOrCreate(
            ['ip_address' => $ipAddress],
            [
                'reason' => $reason,
                'blocked_by' => $blockedBy,
            ],
        );

        $this->clearCache();

        return $blocked;
    }

    public function unblock(BlockedIp $blockedIp): void
    {
        $blockedIp->delete();

        $this->clearCache();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}

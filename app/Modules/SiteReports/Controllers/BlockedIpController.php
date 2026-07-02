<?php

namespace App\Modules\SiteReports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SiteReports\Models\BlockedIp;
use App\Modules\SiteReports\Services\BlockedIpService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlockedIpController extends Controller
{
    public function __construct(
        protected BlockedIpService $blockedIps,
    ) {}

    public function store(Request $request, string $ip): RedirectResponse
    {
        $this->authorize('create', BlockedIp::class);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $blocked = $this->blockedIps->block(
            $ip,
            $request->user()->id,
            $request->input('reason'),
        );

        ActivityLogger::log('site_reports', 'blocked_ip', $blocked, [
            'ip_address' => $blocked->ip_address,
        ]);

        return back()->with('success', "IP address {$blocked->ip_address} has been blocked.");
    }

    public function destroy(BlockedIp $blockedIp): RedirectResponse
    {
        $this->authorize('delete', $blockedIp);

        $ipAddress = $blockedIp->ip_address;

        $this->blockedIps->unblock($blockedIp);

        ActivityLogger::log('site_reports', 'unblocked_ip', null, [
            'ip_address' => $ipAddress,
        ]);

        return back()->with('success', "IP address {$ipAddress} has been unblocked.");
    }
}

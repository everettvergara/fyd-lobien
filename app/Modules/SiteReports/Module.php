<?php

namespace App\Modules\SiteReports;

use App\Modules\SiteReports\Models\BlockedIp;
use App\Modules\SiteReports\Models\SiteVisit;
use App\Modules\SiteReports\Policies\BlockedIpPolicy;
use App\Modules\SiteReports\Policies\SiteVisitPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'SiteReports';
    }

    public function policies(): array
    {
        return [
            SiteVisit::class => SiteVisitPolicy::class,
            BlockedIp::class => BlockedIpPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('site_reports', 'view', 'View Site Reports'),
            $this->permissionEntry('site_reports', 'block', 'Block IP Addresses'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem(
                'Most Visited Pages',
                'admin.site-reports.pages.index',
                'site_reports.view',
                'bi-bar-chart-line',
                'Reports',
                routePattern: 'admin.site-reports.pages.*',
                sort: 10,
            ),
            $this->menuItem(
                'Hits by IP',
                'admin.site-reports.ips.index',
                'site_reports.view',
                'bi-hdd-network',
                'Reports',
                routePattern: 'admin.site-reports.ips.*',
                sort: 20,
            ),
            $this->menuItem(
                'Referring Sites',
                'admin.site-reports.referrers.index',
                'site_reports.view',
                'bi-signpost-split',
                'Reports',
                routePattern: 'admin.site-reports.referrers.*',
                sort: 30,
            ),
        ];
    }
}

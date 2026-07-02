<?php

namespace App\Modules\SEO;

use App\Modules\SEO\Models\SeoSettings;
use App\Modules\SEO\Policies\SeoSettingsPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'SEO';
    }

    public function policies(): array
    {
        return [
            SeoSettings::class => SeoSettingsPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('seo', 'view', 'View SEO'),
            $this->permissionEntry('seo', 'edit', 'Edit SEO'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem(
                'SEO Report',
                'admin.seo.report.index',
                'seo.view',
                'bi-table',
                'SEO',
                routePattern: 'admin.seo.report.*',
                sort: 10,
            ),
            $this->menuItem(
                'Sitemap',
                'admin.seo.sitemap.index',
                'seo.view',
                'bi-diagram-3',
                'SEO',
                routePattern: 'admin.seo.sitemap.*',
                sort: 20,
            ),
        ];
    }
}

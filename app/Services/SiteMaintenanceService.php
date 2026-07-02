<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;

class SiteMaintenanceService
{
    public function __construct(
        protected SettingsService $settings,
    ) {}

    public function enabled(): bool
    {
        if (! Schema::hasTable('settings')) {
            return false;
        }

        $value = $this->settings->get('general', 'maintenance_mode', false);

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function pageUrl(): string
    {
        if (! Schema::hasTable('settings')) {
            return '/site-maintenance';
        }

        $url = $this->settings->get('general', 'maintenance_page_url', '/site-maintenance');

        return is_string($url) && $url !== '' ? $url : '/site-maintenance';
    }
}

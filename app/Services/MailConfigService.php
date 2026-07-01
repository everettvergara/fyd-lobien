<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class MailConfigService
{
    public function __construct(
        protected SettingsService $settings,
    ) {}

    public function apply(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $host = $this->settings->get('email', 'smtp_host');
        if (! $host) {
            return;
        }

        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', $this->settings->get('email', 'smtp_port', 587));
        Config::set('mail.mailers.smtp.username', $this->settings->get('email', 'smtp_username'));
        Config::set('mail.mailers.smtp.password', $this->settings->get('email', 'smtp_password'));
        Config::set('mail.from.address', $this->settings->get('email', 'from_address', config('mail.from.address')));
        Config::set('mail.from.name', $this->settings->get('email', 'from_name', config('mail.from.name')));
    }
}

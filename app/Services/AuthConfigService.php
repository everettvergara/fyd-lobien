<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class AuthConfigService
{
    public function __construct(
        protected SettingsService $settings,
    ) {}

    public function apply(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Config::set('session.lifetime', $this->sessionLifetime());
    }

    public function registrationEnabled(): bool
    {
        if (! config('fyd.registration_enabled', true)) {
            return false;
        }

        if (! Schema::hasTable('settings')) {
            return true;
        }

        $value = $this->settings->get('auth', 'registration_enabled', true);

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function loginMaxAttempts(): int
    {
        if (! Schema::hasTable('settings')) {
            return 5;
        }

        return max(1, (int) $this->settings->get('auth', 'login_max_attempts', 5));
    }

    public function sessionLifetime(): int
    {
        if (! Schema::hasTable('settings')) {
            return (int) config('session.lifetime', 120);
        }

        return max(5, (int) $this->settings->get('auth', 'session_lifetime', 120));
    }
}

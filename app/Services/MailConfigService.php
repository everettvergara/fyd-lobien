<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class MailConfigService
{
    public const ALLOWED_DRIVERS = ['smtp', 'sendmail', 'log'];

    public function __construct(
        protected SettingsService $settings,
    ) {}

    public function apply(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $driver = $this->settings->get('email', 'mail_driver', config('mail.default', 'log'));

        if (! in_array($driver, self::ALLOWED_DRIVERS, true)) {
            $driver = config('mail.default', 'log');
        }

        Config::set('mail.default', $driver);
        Config::set('mail.from.address', $this->settings->get('email', 'from_address', config('mail.from.address')));
        Config::set('mail.from.name', $this->settings->get('email', 'from_name', config('mail.from.name')));

        if ($driver === 'smtp') {
            $encryption = $this->settings->get('email', 'smtp_encryption', '');

            Config::set('mail.mailers.smtp.host', $this->settings->get('email', 'smtp_host', ''));
            Config::set('mail.mailers.smtp.port', (int) $this->settings->get('email', 'smtp_port', 587));
            Config::set('mail.mailers.smtp.username', $this->settings->get('email', 'smtp_username'));
            Config::set('mail.mailers.smtp.password', $this->settings->get('email', 'smtp_password'));
            Config::set('mail.mailers.smtp.scheme', $encryption !== '' ? $encryption : null);
        }

        if ($driver === 'sendmail') {
            Config::set(
                'mail.mailers.sendmail.path',
                $this->settings->get('email', 'sendmail_path', config('mail.mailers.sendmail.path'))
            );
        }
    }
}

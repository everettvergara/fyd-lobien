<?php

namespace App\Modules\Settings\Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds default application settings for new installs.
 *
 * @see docs/SEEDING.md
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'general' => [
                'website_name' => ['Your Website', 'string'],
                'tagline' => ['Welcome to your new website', 'string'],
                'site_logo_id' => ['', 'string'],
                'favicon_id' => ['', 'string'],
                'maintenance_mode' => ['false', 'boolean'],
                'maintenance_page_url' => ['/site-maintenance', 'string'],
            ],
            'email' => [
                'mail_driver' => ['log', 'string'],
                'smtp_host' => ['', 'string'],
                'smtp_port' => ['587', 'string'],
                'smtp_encryption' => ['', 'string'],
                'smtp_username' => ['', 'string'],
                'smtp_password' => ['', 'string'],
                'sendmail_path' => ['/usr/sbin/sendmail -bs -i', 'string'],
                'from_address' => ['hello@example.com', 'string'],
                'from_name' => ['Your Website', 'string'],
            ],
            'contact' => [
                'email' => ['contact@example.com', 'string'],
                'phone' => ['', 'string'],
                'address' => ['', 'string'],
            ],
            'auth' => [
                'registration_enabled' => ['true', 'boolean'],
                'password_min_length' => ['8', 'string'],
                'password_mixed_case' => ['true', 'boolean'],
                'password_numbers' => ['true', 'boolean'],
                'password_symbols' => ['false', 'boolean'],
                'login_max_attempts' => ['5', 'string'],
                'session_lifetime' => ['120', 'string'],
            ],
            'media' => [
                'disk' => ['public', 'string'],
                'storage_provider' => ['local', 'string'],
                'max_upload_kb' => ['51200', 'string'],
                'allowed_mime_types' => [json_encode([
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp',
                    'video/mp4',
                    'audio/mpeg',
                    'audio/wav',
                    'application/pdf',
                    'text/plain',
                    'application/zip',
                ]), 'json'],
                'default_view' => ['grid', 'string'],
            ],
            'seo' => [
                'sitemap_enabled' => ['true', 'boolean'],
                'homepage_include' => ['true', 'boolean'],
                'homepage_changefreq' => ['weekly', 'string'],
                'homepage_priority' => ['1.0', 'string'],
                'default_changefreq_page' => ['monthly', 'string'],
                'default_changefreq_article' => ['weekly', 'string'],
                'default_priority' => ['0.5', 'string'],
            ],
        ];

        foreach ($defaults as $group => $items) {
            foreach ($items as $key => [$value, $type]) {
                Setting::updateOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value, 'type' => $type]
                );
            }
        }
    }
}

<?php

namespace App\Modules\Settings\Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'general' => [
                'website_name' => ['FYD CMS', 'string'],
                'tagline' => ['Professional corporate website platform', 'string'],
            ],
            'email' => [
                'smtp_host' => ['', 'string'],
                'smtp_port' => ['587', 'string'],
                'smtp_username' => ['', 'string'],
                'smtp_password' => ['', 'string'],
                'from_address' => ['hello@example.com', 'string'],
                'from_name' => ['FYD CMS', 'string'],
            ],
            'contact' => [
                'email' => ['contact@example.com', 'string'],
                'phone' => ['', 'string'],
                'address' => ['', 'string'],
            ],
            'seo' => [
                'default_title' => ['FYD CMS', 'string'],
                'default_description' => ['Professional corporate website powered by FYD CMS', 'string'],
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

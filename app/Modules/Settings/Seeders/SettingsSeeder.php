<?php

namespace App\Modules\Settings\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'general' => [
                'website_name' => 'FYD CMS',
                'tagline' => 'Professional corporate website platform',
            ],
            'email' => [
                'smtp_host' => '',
                'smtp_port' => '587',
                'smtp_username' => '',
                'smtp_password' => '',
                'from_address' => 'hello@example.com',
                'from_name' => 'FYD CMS',
            ],
            'contact' => [
                'email' => 'contact@example.com',
                'phone' => '',
                'address' => '',
            ],
            'seo' => [
                'default_title' => 'FYD CMS',
                'default_description' => 'Professional corporate website powered by FYD CMS',
            ],
        ];

        foreach ($defaults as $group => $items) {
            foreach ($items as $key => $value) {
                Setting::updateOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value, 'type' => 'string']
                );
            }
        }
    }
}

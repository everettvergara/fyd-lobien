<?php

namespace App\Modules\Newsletter\Database\Seeders;

use App\Modules\Newsletter\Models\NewsletterList;
use Illuminate\Database\Seeder;

class DemoNewsletterSeeder extends Seeder
{
    public function run(): void
    {
        NewsletterList::updateOrCreate(
            ['slug' => 'site-updates'],
            [
                'name' => 'Site Updates',
                'description' => 'Subscribe to receive news and updates from our site.',
                'is_active' => true,
                'settings' => NewsletterList::defaultSettings(),
            ],
        );
    }
}

<?php

namespace App\Modules\Content\Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\Content\Models\Content;
use Illuminate\Database\Seeder;

/**
 * Creates the published maintenance page required by general.maintenance_page_url.
 *
 * @see docs/SEEDING.md
 */
class SiteMaintenancePageSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->orderBy('id')->first();

        if (! $admin) {
            return;
        }

        $content = Content::updateOrCreate(
            ['slug' => 'site-maintenance'],
            [
                'content_type' => 'page',
                'title' => 'Site Maintenance',
                'summary' => 'We are performing scheduled maintenance.',
                'body' => '<p>Our site is temporarily unavailable while we perform scheduled maintenance. Please check back soon.</p>',
                'status' => ContentStatus::Published,
                'published_at' => now(),
                'author_id' => $admin->id,
            ]
        );

        $content->saveSeo([
            'seo_title' => 'Site Maintenance',
            'meta_description' => 'This site is temporarily unavailable for scheduled maintenance.',
        ]);
    }
}

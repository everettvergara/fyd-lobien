<?php

namespace Database\Seeders;

use App\Modules\Address\Database\Seeders\AddressSeeder;
use App\Modules\Authentication\Seeders\AuthenticationSeeder;
use App\Modules\Banners\Database\Seeders\BannerTemplateSeeder;
use App\Modules\Content\Database\Seeders\SiteMaintenancePageSeeder;
use App\Modules\Permissions\Seeders\PermissionsSeeder;
use App\Modules\Roles\Seeders\RolesSeeder;
use App\Modules\Cache\Database\Seeders\CacheSettingsSeeder;
use App\Modules\Settings\Database\Seeders\SettingsSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Root database seeder for new installs.
 *
 * Runs framework essentials first (RBAC, settings, templates, address data,
 * admin account, maintenance page), then sample content for starter pages.
 *
 * @see docs/SEEDING.md
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Framework essentials
        $this->call([
            PermissionsSeeder::class,
            RolesSeeder::class,
            SettingsSeeder::class,
            CacheSettingsSeeder::class,
            BannerTemplateSeeder::class,
            AddressSeeder::class,
            AuthenticationSeeder::class,
            SiteMaintenancePageSeeder::class,
        ]);

        // Sample content for new projects
        $this->call([
            SampleContentSeeder::class,
            \App\Modules\ContentBlocks\Database\Seeders\ContentBlockSeeder::class,
            \App\Modules\PageManager\Database\Seeders\PageManagerSeeder::class,
        ]);
    }
}

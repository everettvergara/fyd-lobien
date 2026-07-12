<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Setting;
use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Modules\Content\Services\ContentPageSyncService;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Modules\WebForms\Database\Seeders\DemoContactWebformSeeder;
use App\Modules\WebForms\Models\Webform;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds Lobien About, Services, and Contact pages from lobiengroup.com.
 *
 * @see contrib_themes/lobien/README.md
 */
class LobienStaticPagesSeeder extends Seeder
{
    private const SITE_NAME = 'Lobien Realty Group';

    private const ADDRESS = '23F High Street South Corporate Plaza, Tower 1, 26th Street Corner 9th Avenue, Bonifacio Global City, Taguig City, Philippines 1630';

    private const PHONE_PRIMARY = '+63 999 227 7125';

    private const EMAIL = 'inquiry@lobiengroup.com';

    private const MAP_EMBED_URL = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3409.330608884188!2d121.04874297450714!3d14.547904085932247!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c909e6a4a1b1%3A0xeb5bff922a557eb2!2sHigh%20Street%20South%20Corporate%20Plaza%20Tower%201!5e1!3m2!1sen!2sph!4v1736730881493!5m2!1sen!2sph';

    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@fyd.local')->first();

        if ($admin === null) {
            return;
        }

        $this->seedContactSettings();

        if (Schema::hasTable('webforms')) {
            $this->call(DemoContactWebformSeeder::class);
        }

        $pageSync = app(ContentPageSyncService::class);

        foreach ($this->pageDefinitions() as $data) {
            $content = Content::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'content_type' => 'page',
                    'title' => $data['title'],
                    'summary' => $data['summary'],
                    'body' => '',
                    'status' => ContentStatus::Published,
                    'published_at' => now(),
                    'author_id' => $admin->id,
                ]
            );

            $content->saveSeo([
                'seo_title' => $data['seo_title'],
                'meta_description' => $data['meta_description'],
            ]);

            $result = $pageSync->syncContentPage($content->fresh());
            $page = $result['page'] ?? Page::query()->where('path', $data['path'])->first();

            if ($page !== null) {
                $this->configurePageBlocks($page, $data['slug']);
            }
        }
    }

    protected function seedContactSettings(): void
    {
        $settings = [
            'email' => [self::EMAIL, 'string'],
            'phone' => [self::PHONE_PRIMARY, 'string'],
            'address' => [self::ADDRESS, 'string'],
        ];

        foreach ($settings as $key => [$value, $type]) {
            Setting::updateOrCreate(
                ['group' => 'contact', 'key' => $key],
                ['value' => $value, 'type' => $type],
            );
        }
    }

    /**
     * @return list<array<string, string>>
     */
    protected function pageDefinitions(): array
    {
        return [
            [
                'slug' => 'about',
                'path' => '/about',
                'title' => 'About Us',
                'summary' => 'Learn about Lobien Realty Group — our history, vision, mission, and leadership team.',
                'seo_title' => 'About Us — '.self::SITE_NAME,
                'meta_description' => 'Learn about Lobien Realty Group, a full-service Philippine real estate consultancy specializing in commercial leasing, investments, and property acquisition.',
            ],
            [
                'slug' => 'services',
                'path' => '/services',
                'title' => 'Services',
                'summary' => 'Project Leasing, Tenant Solutions, and Property Sale and Acquisition from Lobien Realty Group.',
                'seo_title' => 'Services — '.self::SITE_NAME,
                'meta_description' => 'Explore Lobien Realty Group\'s core services: Project Leasing, Tenant Solutions, and Property Sale and Acquisition.',
            ],
            [
                'slug' => 'contact',
                'path' => '/contact',
                'title' => 'Contact Us',
                'summary' => 'Get in touch with Lobien Realty Group. We\'d love to hear from you.',
                'seo_title' => 'Contact Us — '.self::SITE_NAME,
                'meta_description' => 'Contact Lobien Realty Group for assistance with commercial real estate leasing, investments, and property acquisition in the Philippines.',
            ],
        ];
    }

    protected function configurePageBlocks(Page $page, string $slug): void
    {
        $page->blocks()->delete();

        if ($slug === 'about') {
            $this->seedAboutPageBlocks($page);

            return;
        }

        if ($slug === 'services') {
            $this->seedServicesPageBlocks($page);

            return;
        }

        if ($slug === 'contact') {
            $this->seedContactPageBlocks($page);

            return;
        }
    }

    protected function seedAboutPageBlocks(Page $page): void
    {
        $this->call(LobienAboutBannersSeeder::class);

        $keys = LobienAboutBannersSeeder::pageBannerKeys();
        $heroKey = array_shift($keys);

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'hero',
            'block_type' => 'banner',
            'sort_order' => 0,
            'config' => ['banner_key' => $heroKey],
        ]);

        foreach ($keys as $sortOrder => $bannerKey) {
            PageBlock::create([
                'page_id' => $page->id,
                'region_key' => 'main',
                'block_type' => 'banner',
                'sort_order' => $sortOrder,
                'config' => ['banner_key' => $bannerKey],
            ]);
        }
    }

    protected function seedServicesPageBlocks(Page $page): void
    {
        $this->call(LobienServicesBannersSeeder::class);

        $keys = LobienServicesBannersSeeder::pageBannerKeys();
        $heroKey = array_shift($keys);

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'hero',
            'block_type' => 'banner',
            'sort_order' => 0,
            'config' => ['banner_key' => $heroKey],
        ]);

        foreach ($keys as $sortOrder => $bannerKey) {
            PageBlock::create([
                'page_id' => $page->id,
                'region_key' => 'main',
                'block_type' => 'banner',
                'sort_order' => $sortOrder,
                'config' => ['banner_key' => $bannerKey],
            ]);
        }
    }

    protected function seedContactPageBlocks(Page $page): void
    {
        $this->call(LobienContactBannersSeeder::class);

        $keys = LobienContactBannersSeeder::pageBannerKeys();
        $heroKey = array_shift($keys);

        if ($heroKey !== null) {
            PageBlock::create([
                'page_id' => $page->id,
                'region_key' => 'hero',
                'block_type' => 'banner',
                'sort_order' => 0,
                'config' => ['banner_key' => $heroKey],
            ]);
        }

        $mainSort = 0;

        foreach ($keys as $bannerKey) {
            PageBlock::create([
                'page_id' => $page->id,
                'region_key' => 'main',
                'block_type' => 'banner',
                'sort_order' => $mainSort++,
                'config' => ['banner_key' => $bannerKey],
            ]);
        }

        if (Schema::hasTable('webforms')) {
            PageBlock::create([
                'page_id' => $page->id,
                'region_key' => 'main',
                'block_type' => 'webform',
                'sort_order' => $mainSort++,
                'config' => ['webform_slug' => 'contact-form'],
            ]);

            Webform::query()
                ->where('slug', 'contact-form')
                ->update(['public_page_path' => null]);
        }

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'sidebar',
            'block_type' => 'contact-map',
            'sort_order' => 0,
            'config' => [
                'embed_url' => self::MAP_EMBED_URL,
                'title' => 'Lobien Realty Group office — High Street South Corporate Plaza Tower 1',
            ],
        ]);
    }
}

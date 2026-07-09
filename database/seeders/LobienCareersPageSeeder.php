<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds Lobien Careers page banners and block layout from lobiengroup.com.
 *
 * @see contrib_themes/lobien/README.md
 */
class LobienCareersPageSeeder extends Seeder
{
    private const INDEX_PATH = '/careers';

    private const BLOCK_TYPE = 'careers-listing';

    public function run(): void
    {
        if (! Schema::hasTable('pages')
            || ! Schema::hasTable('page_blocks')
            || ! Schema::hasTable('banners')) {
            return;
        }

        $admin = User::query()->where('email', 'admin@fyd.local')->first();

        if ($admin === null) {
            return;
        }

        $page = $this->ensureCareersPage();

        $this->call(LobienCareersBannersSeeder::class);

        $listingConfig = $page->blocks()
            ->where('block_type', self::BLOCK_TYPE)
            ->value('config') ?? [];

        $page->blocks()->delete();

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'hero',
            'block_type' => 'banner',
            'sort_order' => 0,
            'config' => ['banner_key' => 'careers-header'],
        ]);

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => self::BLOCK_TYPE,
            'sort_order' => 0,
            'config' => $listingConfig,
        ]);
    }

    protected function ensureCareersPage(): Page
    {
        $existing = Page::query()->where('path', self::INDEX_PATH)->first();

        if ($existing !== null) {
            return $existing;
        }

        $page = Page::create([
            'path' => self::INDEX_PATH,
            'slug' => Page::slugFromPath(self::INDEX_PATH),
            'title' => 'Careers',
            'summary' => 'Browse open job opportunities.',
            'body' => '',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $page->saveSeo([
            'seo_title' => 'Careers',
            'meta_description' => 'Browse open job opportunities and apply online.',
            'sitemap_include' => true,
        ]);

        return $page;
    }
}

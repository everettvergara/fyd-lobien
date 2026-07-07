<?php

namespace App\Modules\PageManager\Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\Content\Services\ContentPageSyncService;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Modules\PageManager\Models\PageMaster;
use App\Modules\PageManager\Services\PageManagerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PageManagerSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@fyd.local')->first();

        $master = PageMaster::instance();
        $master->update([
            'default_seo_title_suffix' => ' — Your Website',
            'default_robots' => 'index,follow',
            'default_sitemap_changefreq' => 'monthly',
            'default_sitemap_priority' => 0.5,
            'is_configured' => true,
        ]);

        app(PageManagerService::class)->ensureRootPage();

        $home = Page::query()->where('path', '/')->first();
        if ($home) {
            $home->update([
                'title' => 'Home',
                'summary' => 'Welcome to Your Website',
                'body' => '<p>Your professional corporate website platform powered by FYD CMS.</p>',
                'status' => ContentStatus::Published,
                'published_at' => now(),
                'author_id' => $admin?->id,
            ]);
            $home->saveSeo([
                'seo_title' => 'Your Website',
                'meta_description' => 'Professional corporate website powered by FYD CMS.',
            ]);

            $home->blocks()->delete();
            foreach ([
                ['region_key' => 'hero', 'block_type' => 'banner', 'sort_order' => 0, 'config' => ['banner_key' => 'homepage-hero']],
                ['region_key' => 'main', 'block_type' => 'content-block', 'sort_order' => 0, 'config' => ['content_block_key' => 'featured-pages']],
                ['region_key' => 'main', 'block_type' => 'content-block', 'sort_order' => 1, 'config' => ['content_block_key' => 'latest-articles']],
            ] as $block) {
                PageBlock::create([...$block, 'page_id' => $home->id]);
            }
        }

        if (Schema::hasTable('contents')) {
            app(ContentPageSyncService::class)->syncAll();
        }
    }
}

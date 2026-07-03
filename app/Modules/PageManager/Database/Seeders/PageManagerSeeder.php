<?php

namespace App\Modules\PageManager\Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\Content\Models\Content;
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
                ['region_key' => 'main', 'block_type' => 'featured-content', 'sort_order' => 0, 'config' => ['heading' => 'Featured Content', 'limit' => 3]],
                ['region_key' => 'main', 'block_type' => 'latest-articles', 'sort_order' => 1, 'config' => ['heading' => 'Latest Articles', 'limit' => 3]],
            ] as $block) {
                PageBlock::create([...$block, 'page_id' => $home->id]);
            }
        }

        if (Schema::hasTable('contents')) {
            Content::query()->where('content_type', 'page')->each(function (Content $content) use ($admin) {
                $path = '/'.$content->slug;
                $page = Page::updateOrCreate(
                    ['path' => $path],
                    [
                        'slug' => $content->slug,
                        'title' => $content->title,
                        'summary' => $content->summary,
                        'body' => $content->body,
                        'status' => $content->status,
                        'published_at' => $content->published_at,
                        'author_id' => $content->author_id ?? $admin?->id,
                        'featured_image_id' => $content->featured_image_id,
                    ],
                );

                if ($content->seoMeta) {
                    $page->saveSeo($content->seoMeta->only([
                        'seo_title', 'meta_description', 'meta_keywords', 'canonical_url',
                        'robots', 'og_title', 'og_description', 'og_image_id',
                        'sitemap_include', 'sitemap_changefreq', 'sitemap_priority',
                    ]));
                }

                if ($page->blocks()->count() === 0) {
                    PageBlock::create([
                        'page_id' => $page->id,
                        'region_key' => 'hero',
                        'block_type' => 'banner',
                        'sort_order' => 0,
                        'config' => ['banner_key' => 'page-'.$content->slug],
                    ]);
                    PageBlock::create([
                        'page_id' => $page->id,
                        'region_key' => 'main',
                        'block_type' => 'page-header',
                        'sort_order' => 0,
                        'config' => [],
                    ]);
                    PageBlock::create([
                        'page_id' => $page->id,
                        'region_key' => 'main',
                        'block_type' => 'page-body',
                        'sort_order' => 1,
                        'config' => [],
                    ]);
                }
            });
        }
    }
}

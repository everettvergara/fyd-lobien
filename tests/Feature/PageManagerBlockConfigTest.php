<?php

namespace Tests\Feature;

use App\Enums\BannerType;
use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Models\BannerTemplate;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Services\Public\PublicBlockRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageManagerBlockConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createBanner(string $name, string $key): Banner
    {
        return Banner::create([
            'name' => $name,
            'key' => $key,
            'title' => $name,
            'type' => BannerType::Hero,
            'template_id' => BannerTemplate::query()->value('id'),
            'status' => ContentStatus::Published,
        ]);
    }

    public function test_palette_for_admin_resolves_banner_select_options(): void
    {
        $this->createBanner('Homepage Hero', 'homepage-hero');

        $palette = app(PublicBlockRegistry::class)->paletteForAdmin();
        $bannerBlock = collect($palette)->firstWhere('key', 'banner');

        $this->assertNotNull($bannerBlock);

        $field = collect($bannerBlock['config_schema'])->firstWhere('key', 'banner_key');

        $this->assertSame('select', $field['type']);
        $this->assertArrayHasKey('options', $field);
        $this->assertContains(
            ['value' => 'homepage-hero', 'label' => 'Homepage Hero'],
            $field['options'],
        );
    }

    public function test_palette_for_admin_exposes_content_block_schema_fields(): void
    {
        $palette = app(PublicBlockRegistry::class)->paletteForAdmin();
        $block = collect($palette)->firstWhere('key', 'content-block');

        $this->assertNotNull($block);

        $keys = collect($block['config_schema'])->pluck('key')->all();

        $this->assertSame(['content_block_key'], $keys);
    }

    public function test_default_config_for_content_block_is_empty(): void
    {
        $defaults = app(PublicBlockRegistry::class)->defaultConfigFor('content-block');

        $this->assertSame([], $defaults);
    }

    public function test_admin_page_update_persists_typed_block_config(): void
    {
        $admin = User::where('email', 'admin@fyd.local')->firstOrFail();
        $page = Page::query()->where('path', '/about')->firstOrFail();

        $response = $this->actingAs($admin)->put(route('admin.pages.update', $page), [
            'path' => $page->path,
            'title' => $page->title,
            'summary' => $page->summary,
            'body' => $page->body,
            'status' => $page->status->value,
            'blocks' => [
                [
                    'region_key' => 'main',
                    'block_type' => 'content-block',
                    'sort_order' => 0,
                    'config' => [
                        'content_block_key' => 'featured-pages',
                    ],
                ],
                [
                    'region_key' => 'main',
                    'block_type' => 'page-header',
                    'sort_order' => 1,
                    'config' => [],
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.pages.index'));

        $this->assertDatabaseHas('page_blocks', [
            'page_id' => $page->id,
            'block_type' => 'content-block',
            'region_key' => 'main',
        ]);

        $block = PageBlock::query()
            ->where('page_id', $page->id)
            ->where('block_type', 'content-block')
            ->firstOrFail();

        $this->assertSame([
            'content_block_key' => 'featured-pages',
        ], $block->config);

        $header = PageBlock::query()
            ->where('page_id', $page->id)
            ->where('block_type', 'page-header')
            ->firstOrFail();

        $this->assertSame([], $header->config ?? []);
    }

    public function test_page_edit_includes_block_palette_json_for_schema_rendering(): void
    {
        $this->createBanner('Promo Banner', 'promo-banner');

        $admin = User::where('email', 'admin@fyd.local')->firstOrFail();
        $page = Page::query()->where('path', '/')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.pages.edit', $page))
            ->assertOk()
            ->assertSee('data-block-palette-json', false)
            ->assertSee('promo-banner', false)
            ->assertSee('Content Block', false)
            ->assertSee('featured-pages', false);
    }
}

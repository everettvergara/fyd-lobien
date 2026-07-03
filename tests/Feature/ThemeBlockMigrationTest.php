<?php

namespace Tests\Feature;

use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Modules\PageManager\Models\PageMaster;
use App\Modules\PageManager\Models\PageMasterBlock;
use App\Services\Theme\ThemeBlockMigrationService;
use App\Services\Theme\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ThemeBlockMigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, string> */
    protected array $fixtureSlugs = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function tearDown(): void
    {
        foreach ($this->fixtureSlugs as $slug) {
            $path = base_path("themes/{$slug}");

            if (is_dir($path)) {
                File::deleteDirectory($path);
            }
        }

        app(ThemeService::class)->setActive('fyd-default');

        parent::tearDown();
    }

    public function test_regions_for_slug_reads_installed_theme_manifest(): void
    {
        $keys = app(ThemeService::class)->regionKeysForSlug('fyd-default');

        $this->assertSame(['hero', 'main', 'sidebar', 'footer'], $keys);
    }

    public function test_switching_to_theme_with_same_regions_preserves_blocks(): void
    {
        $lobien = app(ThemeService::class);

        if (! in_array('lobien', app(\App\Services\Theme\ThemeRegistryService::class)->installed()->pluck('slug')->all(), true)) {
            $this->markTestSkipped('lobien theme is not installed.');
        }

        $home = Page::query()->where('path', '/')->firstOrFail();
        $before = PageBlock::query()->orderBy('page_id')->orderBy('region_key')->orderBy('sort_order')->get()
            ->map(fn (PageBlock $block) => $block->only(['page_id', 'region_key', 'block_type', 'sort_order']))
            ->all();

        $this->assertNotEmpty($before);

        $summary = app(ThemeService::class)->setActive('lobien');

        $this->assertSame('lobien', app(ThemeService::class)->activeSlug());
        $this->assertIsArray($summary);
        $this->assertSame(0, $summary['remapped']);

        $after = PageBlock::query()->orderBy('page_id')->orderBy('region_key')->orderBy('sort_order')->get()
            ->map(fn (PageBlock $block) => $block->only(['page_id', 'region_key', 'block_type', 'sort_order']))
            ->all();

        $this->assertSame($before, $after);
        $this->assertSame(
            PageBlock::count() + PageMasterBlock::count(),
            $summary['preserved'],
        );
    }

    public function test_orphan_blocks_move_to_main_when_region_removed(): void
    {
        $slug = 'test-no-sidebar';
        $this->installFixtureTheme($slug, [
            'regions' => [
                ['key' => 'hero', 'label' => 'Hero', 'description' => 'Hero'],
                ['key' => 'main', 'label' => 'Main', 'description' => 'Main'],
                ['key' => 'footer', 'label' => 'Footer', 'description' => 'Footer'],
            ],
        ]);

        $page = Page::query()->where('path', '/about')->firstOrFail();
        $block = PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'sidebar',
            'block_type' => 'page-body',
            'sort_order' => 0,
            'config' => [],
        ]);

        $summary = app(ThemeService::class)->setActive($slug);

        $block->refresh();

        $this->assertSame('main', $block->region_key);
        $this->assertIsArray($summary);
        $this->assertSame(1, $summary['remapped']);
        $this->assertContains('sidebar → main', $summary['details']);
    }

    public function test_region_map_moves_blocks_to_mapped_target(): void
    {
        $slug = 'test-region-map';
        $this->installFixtureTheme($slug, [
            'regions' => [
                ['key' => 'hero', 'label' => 'Hero', 'description' => 'Hero'],
                ['key' => 'main', 'label' => 'Main', 'description' => 'Main'],
                ['key' => 'footer', 'label' => 'Footer', 'description' => 'Footer'],
            ],
            'region_map' => [
                'sidebar' => 'footer',
            ],
        ]);

        $page = Page::query()->where('path', '/about')->firstOrFail();
        $block = PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'sidebar',
            'block_type' => 'page-body',
            'sort_order' => 0,
            'config' => [],
        ]);

        app(ThemeService::class)->setActive($slug);

        $block->refresh();

        $this->assertSame('footer', $block->region_key);
    }

    public function test_page_master_blocks_are_migrated(): void
    {
        $slug = 'test-master-migrate';
        $this->installFixtureTheme($slug, [
            'regions' => [
                ['key' => 'hero', 'label' => 'Hero', 'description' => 'Hero'],
                ['key' => 'main', 'label' => 'Main', 'description' => 'Main'],
            ],
        ]);

        $master = PageMaster::instance();
        $block = PageMasterBlock::create([
            'page_master_id' => $master->id,
            'region_key' => 'sidebar',
            'block_type' => 'page-header',
            'sort_order' => 0,
            'config' => [],
        ]);

        $this->assertSame(['hero', 'main'], app(ThemeService::class)->regionKeysForSlug($slug));

        $summary = app(ThemeBlockMigrationService::class)->migrate('fyd-default', $slug);

        $block->refresh();

        $this->assertSame(1, $summary['remapped']);
        $this->assertSame('main', $block->region_key);
    }

    public function test_activating_same_theme_returns_null_summary(): void
    {
        $summary = app(ThemeService::class)->setActive('fyd-default');

        $this->assertNull($summary);
    }

    public function test_migration_service_reindexes_sort_order_per_region(): void
    {
        $page = Page::query()->where('path', '/about')->firstOrFail();

        PageBlock::query()->where('page_id', $page->id)->delete();

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'sidebar',
            'block_type' => 'page-header',
            'sort_order' => 5,
            'config' => [],
        ]);
        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'sidebar',
            'block_type' => 'page-body',
            'sort_order' => 9,
            'config' => [],
        ]);

        $slug = 'test-reindex';
        $this->installFixtureTheme($slug, [
            'regions' => [
                ['key' => 'main', 'label' => 'Main', 'description' => 'Main'],
            ],
        ]);

        app(ThemeBlockMigrationService::class)->migrate('fyd-default', $slug);

        $orders = PageBlock::query()
            ->where('page_id', $page->id)
            ->where('region_key', 'main')
            ->orderBy('sort_order')
            ->pluck('sort_order')
            ->all();

        $this->assertSame([0, 1], $orders);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function installFixtureTheme(string $slug, array $overrides): void
    {
        $this->fixtureSlugs[] = $slug;

        $source = base_path('themes/fyd-default');
        $target = base_path("themes/{$slug}");

        if (is_dir($target)) {
            File::deleteDirectory($target);
        }

        File::copyDirectory($source, $target);

        $manifest = json_decode((string) file_get_contents("{$target}/theme.json"), true);
        unset($manifest['protected']);
        $manifest = array_replace($manifest, $overrides);
        $manifest['slug'] = $slug;
        $manifest['name'] = (string) ($overrides['name'] ?? $slug);

        file_put_contents("{$target}/theme.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Modules\ContentBlocks\Database\Seeders\ContentBlockSeeder;
use App\Modules\ContentBlocks\Enums\ContentBlockFormatter;
use App\Modules\ContentBlocks\Models\ContentBlock;
use App\Modules\ContentBlocks\Services\ContentBlockRenderingService;
use App\Framework\MenuRegistry;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Services\Public\PublicBlockRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentBlockManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        $this->seed();

        return User::where('email', 'admin@fyd.local')->firstOrFail();
    }

    public function test_seeder_creates_default_content_blocks(): void
    {
        $this->admin();

        $this->assertDatabaseHas('content_blocks', ['key' => 'latest-articles', 'status' => ContentStatus::Published->value, 'icon' => 'bi-newspaper']);
        $this->assertDatabaseHas('content_blocks', ['key' => 'featured-pages', 'status' => ContentStatus::Published->value, 'icon' => 'bi-grid']);
    }

    public function test_menu_item_uses_seeded_icon(): void
    {
        $admin = $this->admin();

        $item = collect(app(MenuRegistry::class)->panelsFor($admin)['core'])
            ->flatMap(fn (array $section) => $section['items'])
            ->firstWhere('label', 'Content Blocks');

        $this->assertNotNull($item);
        $this->assertSame(ContentBlockSeeder::MENU_ICON, $item['icon']);
        $this->assertSame('bi bi-grid-fill', admin_icon($item['icon']));
    }

    public function test_admin_creates_content_block_definition(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post('/admin/content-blocks', [
            'name' => 'All Articles Table',
            'key' => 'all-articles-table',
            'icon' => 'bi-table',
            'status' => ContentStatus::Published->value,
            'content_types' => ['article'],
            'fields' => [
                [
                    'field' => 'title',
                    'label' => 'Title',
                    'class' => 'content-block__title',
                    'id' => 'content-block-all-articles-table-title',
                    'sort_order' => 0,
                ],
                [
                    'field' => 'summary',
                    'label' => 'Summary',
                    'class' => 'content-block__summary',
                    'id' => 'content-block-all-articles-table-summary',
                    'sort_order' => 1,
                ],
            ],
            'filters' => [],
            'sort_field' => 'published_at',
            'sort_direction' => 'desc',
            'items_per_page' => 5,
            'pagination_enabled' => false,
            'formatter' => ContentBlockFormatter::Table->value,
        ]);

        $response->assertRedirect(route('admin.content-blocks.edit', ContentBlock::where('key', 'all-articles-table')->firstOrFail()));

        $this->assertDatabaseHas('content_blocks', [
            'key' => 'all-articles-table',
            'formatter' => ContentBlockFormatter::Table->value,
        ]);
    }

    public function test_rendering_service_returns_formatter_payload_with_class_hooks(): void
    {
        $this->admin();

        $dto = app(ContentBlockRenderingService::class)->contentBlockByKey('latest-articles');

        $this->assertNotNull($dto);
        $this->assertSame('unformatted', $dto['formatter']);
        $this->assertSame('content-block content-block--latest-articles', $dto['wrapperClass']);
        $this->assertNotEmpty($dto['fields']);
        $this->assertSame('content-block__title', $dto['fields'][0]['class']);
        $this->assertNotEmpty($dto['rows']);
    }

    public function test_draft_content_block_is_not_public(): void
    {
        $this->admin();

        ContentBlock::query()->where('key', 'latest-articles')->update(['status' => ContentStatus::Draft]);

        $this->assertNull(app(ContentBlockRenderingService::class)->contentBlockByKey('latest-articles'));
    }

    public function test_filter_operator_limits_results(): void
    {
        $admin = $this->admin();

        Content::create([
            'content_type' => 'article',
            'title' => 'Unique Filter Target',
            'slug' => 'unique-filter-target',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $admin->id,
        ]);

        $block = ContentBlock::create([
            'name' => 'Filtered Articles',
            'key' => 'filtered-articles',
            'status' => ContentStatus::Published,
            'content_types' => ['article'],
            'fields' => [
                ['field' => 'title', 'label' => 'Title', 'class' => 'content-block__title', 'id' => 'content-block-filtered-articles-title', 'sort_order' => 0],
            ],
            'filters' => [
                ['field' => 'title', 'operator' => 'contains', 'value' => 'Unique Filter Target', 'group' => 'and'],
            ],
            'sort_field' => 'published_at',
            'sort_direction' => 'desc',
            'items_per_page' => 10,
            'pagination_enabled' => false,
            'formatter' => ContentBlockFormatter::Unformatted,
        ]);

        $dto = app(ContentBlockRenderingService::class)->dto($block);

        $this->assertCount(1, $dto['rows']);
        $this->assertSame('Unique Filter Target', $dto['rows'][0][0]['value']);
    }

    public function test_preview_retrieves_matching_content(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->postJson('/admin/content-blocks/preview', [
            'key' => 'preview-test',
            'name' => 'Preview Test',
            'icon' => 'bi-grid',
            'status' => ContentStatus::Draft->value,
            'content_types' => ['article'],
            'fields' => [
                [
                    'field' => 'title',
                    'label' => 'Title',
                    'class' => 'content-block__title',
                    'id' => 'content-block-preview-test-title',
                    'sort_order' => 0,
                ],
            ],
            'filters' => [],
            'sort_field' => 'published_at',
            'sort_direction' => 'desc',
            'items_per_page' => 5,
            'pagination_enabled' => false,
            'formatter' => ContentBlockFormatter::Table->value,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'meta',
                'metaHtml',
                'html',
                'sql' => ['countSql', 'dataSql'],
                'sqlHtml',
            ])
            ->assertJsonPath('meta.formatter', ContentBlockFormatter::Table->value);

        $this->assertGreaterThanOrEqual(0, $response->json('meta.totalMatching'));
        $this->assertStringContainsString('contents', $response->json('sql.countSql'));
        $this->assertStringContainsString('contents', $response->json('sql.dataSql'));
    }

    public function test_create_page_includes_preview_controls(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.content-blocks.create'))
            ->assertOk()
            ->assertSee('id="auto-update-preview"', false)
            ->assertSee('id="show-sql-preview"', false)
            ->assertSee('id="preview-sql-section"', false)
            ->assertSee('id="contentBlockPreviewPanels"', false)
            ->assertSee('Preview / Retrieve', false)
            ->assertSee('Auto Update Preview on save', false);
    }

    public function test_edit_page_includes_initial_preview(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.content-blocks.edit', ContentBlock::where('key', 'latest-articles')->firstOrFail()))
            ->assertOk()
            ->assertSee('Preview / Retrieve', false)
            ->assertSee('Retrieve', false)
            ->assertSee('content-block-preview', false)
            ->assertSee('id="auto-update-preview"', false)
            ->assertSee('id="show-sql-preview"', false)
            ->assertSee('id="preview-sql-section"', false)
            ->assertSee('-- Count query', false);
    }

    public function test_palette_for_admin_exposes_content_block_select(): void
    {
        $this->admin();

        $palette = app(PublicBlockRegistry::class)->paletteForAdmin();
        $block = collect($palette)->firstWhere('key', 'content-block');

        $this->assertNotNull($block);

        $field = collect($block['config_schema'])->firstWhere('key', 'content_block_key');

        $this->assertSame('select', $field['type']);
        $this->assertArrayHasKey('options', $field);
        $this->assertTrue(collect($field['options'])->contains(
            fn (array $option) => $option['value'] === 'latest-articles' && $option['label'] === 'Latest Articles',
        ));
    }

    public function test_homepage_uses_content_block_placements_after_seed(): void
    {
        $this->admin();

        $home = Page::query()->where('path', '/')->firstOrFail();

        $this->assertDatabaseHas('page_blocks', [
            'page_id' => $home->id,
            'block_type' => 'content-block',
            'region_key' => 'main',
        ]);
    }

    public function test_admin_page_update_persists_content_block_config(): void
    {
        $admin = $this->admin();
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
            ],
        ]);

        $response->assertRedirect(route('admin.pages.index'));

        $block = PageBlock::query()
            ->where('page_id', $page->id)
            ->where('block_type', 'content-block')
            ->firstOrFail();

        $this->assertSame(['content_block_key' => 'featured-pages'], $block->config);
    }
}

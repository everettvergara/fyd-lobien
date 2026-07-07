<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Modules\PageManager\Models\PageMaster;
use App\Modules\PageManager\Models\PageMasterBlock;
use App\Modules\PageManager\Services\PageManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_root_page_is_created_and_cannot_be_deleted_by_policy(): void
    {
        $page = app(PageManagerService::class)->ensureRootPage();

        $this->assertSame('/', $page->path);
        $this->assertTrue($page->is_system);

        $admin = User::where('email', 'admin@fyd.local')->first();
        $this->assertFalse($admin->can('delete', $page));
    }

    public function test_published_page_renders_via_page_manager_route(): void
    {
        $page = Page::query()->where('path', '/about')->firstOrFail();

        PageBlock::firstOrCreate(
            ['page_id' => $page->id, 'region_key' => 'main', 'block_type' => 'page-body'],
            ['sort_order' => 0, 'config' => []],
        );

        $this->get('/about')
            ->assertOk()
            ->assertInertia(fn ($assert) => $assert
                ->component('Page/Show')
                ->where('page.title', $page->title)
                ->has('regions.main'));
    }

    public function test_unregistered_blocks_are_omitted_from_render_payload(): void
    {
        $page = Page::query()->where('path', '/')->firstOrFail();

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'footer',
            'block_type' => 'newsletter',
            'sort_order' => 0,
            'config' => ['list_slug' => 'site-updates'],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($assert) => $assert
                ->component('Page/Show')
                ->missing('regions.footer'));
    }

    public function test_region_order_follows_theme_not_merge_key_order(): void
    {
        $page = Page::query()->where('path', '/')->firstOrFail();
        $page->blocks()->delete();

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'hero',
            'block_type' => 'page-body',
            'sort_order' => 0,
            'config' => [],
        ]);

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => 'page-body',
            'sort_order' => 0,
            'config' => [],
        ]);

        $master = PageMaster::instance();
        $master->blocks()->delete();
        PageMasterBlock::create([
            'page_master_id' => $master->id,
            'region_key' => 'footer',
            'block_type' => 'page-body',
            'sort_order' => 0,
            'config' => [],
        ]);

        $payload = app(\App\Services\Public\PageRenderService::class)->render($page);

        $this->assertSame(['hero', 'main', 'footer'], $payload['regionOrder']);
    }

    public function test_misconfigured_module_blocks_are_omitted_from_render_payload(): void
    {
        $page = Page::query()->where('path', '/')->firstOrFail();

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'footer',
            'block_type' => 'newsletter',
            'sort_order' => 0,
            'config' => [],
        ]);

        $payload = app(\App\Services\Public\PageRenderService::class)->render($page);

        $this->assertArrayNotHasKey('footer', $payload['regions']);
    }

    public function test_admin_can_list_pages_with_permission(): void
    {
        app(PageManagerService::class)->ensureRootPage();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->get('/admin/pages')
            ->assertOk();
    }

    public function test_admin_can_open_create_page_form(): void
    {
        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->get(route('admin.pages.create'))
            ->assertOk()
            ->assertSee('Create Page');
    }

    public function test_pages_list_shows_edit_action_for_authorized_users(): void
    {
        $page = Page::query()->where('path', '/about')->firstOrFail();
        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->get('/admin/pages')
            ->assertOk()
            ->assertSee(route('admin.pages.edit', $page), false)
            ->assertSee('title="Edit"', false);
    }
}

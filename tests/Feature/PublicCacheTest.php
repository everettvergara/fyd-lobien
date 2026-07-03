<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Modules\Cache\Services\PublicCacheService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicCacheTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@fyd.local')->first();
    }

    public function test_homepage_response_is_cached_when_enabled(): void
    {
        $this->enablePublicCache();

        $this->get('/');
        $index = Cache::get(PublicCacheService::KEY_PREFIX.'_index', []);

        $this->assertNotEmpty($index);

        $this->get('/');
        $this->assertCount(1, Cache::get(PublicCacheService::KEY_PREFIX.'_index', []));
    }

    public function test_published_content_page_is_cached(): void
    {
        $this->enablePublicCache();

        $page = Page::create([
            'path' => '/cached-page',
            'slug' => 'cached-page',
            'title' => 'Cached Page',
            'body' => '<p>Cached content</p>',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => 'page-body',
            'sort_order' => 0,
            'config' => [],
        ]);

        $this->get('/cached-page');

        $this->assertNotEmpty(Cache::get(PublicCacheService::KEY_PREFIX.'_index', []));
    }

    public function test_search_is_not_cached(): void
    {
        $this->enablePublicCache();

        $this->get('/search?q=hello');

        $this->assertEmpty(Cache::get(PublicCacheService::KEY_PREFIX.'_index', []));
    }

    public function test_admin_routes_are_not_cached(): void
    {
        $this->enablePublicCache();

        $this->actingAs($this->admin)->get('/admin');

        $this->assertEmpty(Cache::get(PublicCacheService::KEY_PREFIX.'_index', []));
    }

    public function test_disabled_setting_bypasses_cache(): void
    {
        app(SettingsService::class)->set('cache', 'enabled', 'false', 'boolean');

        $this->get('/');

        $this->assertEmpty(Cache::get(PublicCacheService::KEY_PREFIX.'_index', []));
    }

    public function test_admin_can_clear_public_cache(): void
    {
        $this->enablePublicCache();
        $this->get('/');

        $this->assertNotEmpty(Cache::get(PublicCacheService::KEY_PREFIX.'_index', []));

        $response = $this->actingAs($this->admin)->post('/admin/cache/clear');

        $response->assertRedirect(route('admin.cache.index'));
        $this->assertEmpty(Cache::get(PublicCacheService::KEY_PREFIX.'_index', []));
    }

    public function test_admin_can_view_cache_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/cache');

        $response->assertOk();
        $response->assertSee('Cache Settings');
        $response->assertSee('Enable public page caching');
    }

    public function test_admin_can_update_cache_settings(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/cache', [
            'enabled' => '1',
            'ttl_days' => '7',
        ]);

        $response->assertRedirect(route('admin.cache.index'));

        $settings = app(SettingsService::class);
        $this->assertTrue((bool) $settings->get('cache', 'enabled'));
        $this->assertSame('7', $settings->get('cache', 'ttl_days'));
    }

    public function test_cache_settings_require_permission(): void
    {
        $author = User::factory()->create(['status' => \App\Enums\UserStatus::Active]);
        $authorRole = Role::where('name', 'author')->first();
        $author->syncRoles([$authorRole->id]);

        $response = $this->actingAs($author)->get('/admin/cache');

        $response->assertForbidden();
    }

    public function test_default_cache_settings_are_seeded(): void
    {
        $this->assertDatabaseHas('settings', [
            'group' => 'cache',
            'key' => 'enabled',
            'value' => 'true',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'cache',
            'key' => 'ttl_days',
            'value' => '1',
        ]);
    }

    public function test_public_cache_service_ttl_defaults_to_one_day(): void
    {
        $ttl = app(PublicCacheService::class)->ttl();

        $this->assertSame(86400, $ttl);
    }

    protected function enablePublicCache(): void
    {
        Setting::updateOrCreate(
            ['group' => 'cache', 'key' => 'enabled'],
            ['value' => 'true', 'type' => 'boolean'],
        );

        app(SettingsService::class)->forget('cache', 'enabled');
    }
}

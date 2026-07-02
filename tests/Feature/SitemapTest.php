<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Setting;
use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@fyd.local')->first();
    }

    public function test_sitemap_returns_valid_xml_with_homepage_and_published_content(): void
    {
        $content = Content::create([
            'content_type' => 'page',
            'title' => 'About Us',
            'slug' => 'about-us',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->admin->id,
        ]);

        $content->saveSeo([
            'seo_title' => 'About Us',
            'sitemap_include' => true,
            'sitemap_changefreq' => 'monthly',
            'sitemap_priority' => 0.8,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<urlset', false);
        $response->assertSee(url('/'), false);
        $response->assertSee(url('/about-us'), false);
        $response->assertSee('<changefreq>monthly</changefreq>', false);
        $response->assertSee('<priority>0.8</priority>', false);
    }

    public function test_draft_content_is_excluded_from_sitemap(): void
    {
        Content::create([
            'content_type' => 'page',
            'title' => 'Draft Page',
            'slug' => 'draft-page',
            'status' => ContentStatus::Draft,
            'author_id' => $this->admin->id,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertDontSee(url('/draft-page'), false);
    }

    public function test_content_with_sitemap_include_false_is_excluded(): void
    {
        $content = Content::create([
            'content_type' => 'page',
            'title' => 'Hidden Page',
            'slug' => 'hidden-page',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->admin->id,
        ]);

        $content->saveSeo(['sitemap_include' => false]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertDontSee(url('/hidden-page'), false);
    }

    public function test_content_with_noindex_is_excluded(): void
    {
        $content = Content::create([
            'content_type' => 'page',
            'title' => 'Private Page',
            'slug' => 'private-page',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->admin->id,
        ]);

        $content->saveSeo(['robots' => 'noindex,follow']);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertDontSee(url('/private-page'), false);
    }

    public function test_robots_txt_references_sitemap_url(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('Sitemap: '.url('/sitemap.xml'), false);
    }

    public function test_admin_can_update_sitemap_settings(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/seo/sitemap', [
            'sitemap_enabled' => true,
            'homepage_include' => true,
            'homepage_changefreq' => 'daily',
            'homepage_priority' => 1.0,
            'default_changefreq_page' => 'monthly',
            'default_changefreq_article' => 'weekly',
            'default_priority' => 0.5,
        ]);

        $response->assertRedirect(route('admin.seo.sitemap.index'));
        $this->assertEquals('daily', app(SettingsService::class)->get('seo', 'homepage_changefreq'));
    }

    public function test_sitemap_disabled_globally_returns_404(): void
    {
        Setting::updateOrCreate(
            ['group' => 'seo', 'key' => 'sitemap_enabled'],
            ['value' => 'false', 'type' => 'boolean']
        );
        app(SettingsService::class)->forget('seo', 'sitemap_enabled');

        $response = $this->get('/sitemap.xml');

        $response->assertNotFound();
    }

    public function test_seo_menu_items_visible_for_authorized_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/seo/report');

        $response->assertOk();
        $response->assertSee('SEO Report', false);

        $response = $this->actingAs($this->admin)->get('/admin/seo/sitemap');

        $response->assertOk();
        $response->assertSee('Sitemap Settings', false);
    }
}

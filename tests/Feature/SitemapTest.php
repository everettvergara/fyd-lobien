<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Setting;
use App\Models\User;
use App\Modules\PageManager\Models\Page;
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

    public function test_sitemap_returns_valid_xml_with_homepage_and_published_pages(): void
    {
        $page = Page::create([
            'path' => '/about-us',
            'slug' => 'about-us',
            'title' => 'About Us',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->admin->id,
        ]);

        $page->saveSeo([
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

    public function test_draft_page_is_excluded_from_sitemap(): void
    {
        Page::create([
            'path' => '/draft-page',
            'slug' => 'draft-page',
            'title' => 'Draft Page',
            'status' => ContentStatus::Draft,
            'author_id' => $this->admin->id,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertDontSee(url('/draft-page'), false);
    }

    public function test_page_with_sitemap_include_false_is_excluded(): void
    {
        $page = Page::create([
            'path' => '/hidden-page',
            'slug' => 'hidden-page',
            'title' => 'Hidden Page',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->admin->id,
        ]);

        $page->saveSeo(['sitemap_include' => false]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertDontSee(url('/hidden-page'), false);
    }

    public function test_sitemap_returns_404_when_disabled(): void
    {
        Setting::updateOrCreate(
            ['group' => 'seo', 'key' => 'sitemap_enabled'],
            ['value' => '0', 'type' => 'boolean'],
        );

        $this->get('/sitemap.xml')->assertNotFound();
    }
}

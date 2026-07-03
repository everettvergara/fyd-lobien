<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\PageManager\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@fyd.local')->first();
    }

    public function test_seo_report_loads_for_authorized_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/seo/report');

        $response->assertOk();
        $response->assertSee('SEO Report', false);
    }

    public function test_seo_report_lists_pages_with_seo_meta_columns(): void
    {
        $page = Page::create([
            'path' => '/seo-report-page',
            'slug' => 'seo-report-page',
            'title' => 'SEO Report Page',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->admin->id,
        ]);

        $page->saveSeo([
            'seo_title' => 'Custom SEO Title',
            'meta_description' => 'A detailed meta description for testing.',
            'meta_keywords' => 'seo, test',
            'robots' => 'index,follow',
            'sitemap_include' => true,
            'sitemap_changefreq' => 'weekly',
            'sitemap_priority' => 0.7,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/seo/report');

        $response->assertOk();
        $response->assertSee('SEO Report Page', false);
        $response->assertSee('Custom SEO Title', false);
        $response->assertSee('A detailed meta description', false);
        $response->assertSee('Included', false);
    }

    public function test_page_without_seo_meta_appears_with_placeholders(): void
    {
        Page::create([
            'path' => '/no-seo-meta-page',
            'slug' => 'no-seo-meta-page',
            'title' => 'No SEO Meta Page',
            'status' => ContentStatus::Draft,
            'author_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/seo/report');

        $response->assertOk();
        $response->assertSee('No SEO Meta Page', false);
        $response->assertSee('Missing', false);
        $response->assertSee('index,follow', false);
    }

    public function test_title_links_and_edit_action_point_to_page_edit(): void
    {
        $page = Page::create([
            'path' => '/editable-seo-page',
            'slug' => 'editable-seo-page',
            'title' => 'Editable SEO Page',
            'status' => ContentStatus::Draft,
            'author_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/seo/report');

        $response->assertOk();
        $response->assertSee(route('admin.pages.edit', $page), false);
    }

    public function test_unauthorized_user_cannot_access_seo_report(): void
    {
        $viewer = User::factory()->create();

        $response = $this->actingAs($viewer)->get('/admin/seo/report');

        $response->assertRedirect('/admin/access-pending');
    }
}

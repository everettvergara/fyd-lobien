<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\Content\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@fyd.local')->first();
        $this->author = User::where('email', 'author@fyd.local')->first();
    }

    public function test_seo_report_loads_for_authorized_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/seo/report');

        $response->assertOk();
        $response->assertSee('SEO Report', false);
    }

    public function test_seo_report_lists_content_with_seo_meta_columns(): void
    {
        $content = Content::create([
            'content_type' => 'page',
            'title' => 'SEO Report Page',
            'slug' => 'seo-report-page',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->admin->id,
        ]);

        $content->saveSeo([
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
        $response->assertSee('Weekly', false);
        $response->assertSee('0.7', false);
    }

    public function test_content_without_seo_meta_appears_with_placeholders(): void
    {
        Content::create([
            'content_type' => 'article',
            'title' => 'No SEO Meta Article',
            'slug' => 'no-seo-meta-article',
            'status' => ContentStatus::Draft,
            'author_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/seo/report');

        $response->assertOk();
        $response->assertSee('No SEO Meta Article', false);
        $response->assertSee('Missing', false);
        $response->assertSee('index,follow', false);
    }

    public function test_title_links_and_edit_action_point_to_content_edit(): void
    {
        $content = Content::create([
            'content_type' => 'page',
            'title' => 'Editable SEO Content',
            'slug' => 'editable-seo-content',
            'status' => ContentStatus::Draft,
            'author_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/seo/report');

        $response->assertOk();
        $response->assertSee(route('admin.content.edit', $content), false);
        $response->assertSee('aria-label="Edit"', false);
    }

    public function test_sitemap_excluded_filter_narrows_results(): void
    {
        $included = Content::create([
            'content_type' => 'page',
            'title' => 'Included In Sitemap',
            'slug' => 'included-in-sitemap',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->admin->id,
        ]);
        $included->saveSeo(['sitemap_include' => true]);

        $excluded = Content::create([
            'content_type' => 'page',
            'title' => 'Excluded From Sitemap',
            'slug' => 'excluded-from-sitemap',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->admin->id,
        ]);
        $excluded->saveSeo(['sitemap_include' => false]);

        $response = $this->actingAs($this->admin)->get('/admin/seo/report?sitemap_include=excluded');

        $response->assertOk();
        $response->assertSee('Excluded From Sitemap', false);
        $response->assertDontSee('Included In Sitemap', false);
    }

    public function test_noindex_filter_narrows_results(): void
    {
        $indexable = Content::create([
            'content_type' => 'page',
            'title' => 'Indexable Content',
            'slug' => 'indexable-content',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->admin->id,
        ]);
        $indexable->saveSeo(['robots' => 'index,follow']);

        $noindex = Content::create([
            'content_type' => 'page',
            'title' => 'Noindex Content',
            'slug' => 'noindex-content',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->admin->id,
        ]);
        $noindex->saveSeo(['robots' => 'noindex,follow']);

        $response = $this->actingAs($this->admin)->get('/admin/seo/report?robots=noindex');

        $response->assertOk();
        $response->assertSee('Noindex Content', false);
        $response->assertDontSee('Indexable Content', false);
    }

    public function test_author_with_own_content_only_sees_their_content(): void
    {
        Content::create([
            'content_type' => 'page',
            'title' => 'Admin Only Page',
            'slug' => 'admin-only-page',
            'status' => ContentStatus::Draft,
            'author_id' => $this->admin->id,
        ]);

        Content::create([
            'content_type' => 'article',
            'title' => 'Author Article',
            'slug' => 'author-article',
            'status' => ContentStatus::Draft,
            'author_id' => $this->author->id,
        ]);

        $response = $this->actingAs($this->author)->get('/admin/seo/report');

        $response->assertOk();
        $response->assertSee('Author Article', false);
        $response->assertDontSee('Admin Only Page', false);
    }

    public function test_unauthorized_user_cannot_access_seo_report(): void
    {
        $viewer = User::where('email', 'viewer@fyd.local')->first();

        $response = $this->actingAs($viewer)->get('/admin/seo/report');

        $response->assertForbidden();
    }
}

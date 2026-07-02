<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\Content\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ContentAdminListTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        $this->seed();

        return User::where('email', 'admin@fyd.local')->first();
    }

    public function test_content_list_renders_standard_columns_and_inline_actions(): void
    {
        $admin = $this->admin();

        Content::create([
            'content_type' => 'page',
            'title' => 'Standard List Content',
            'slug' => 'standard-list-content',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/content');

        $response->assertOk();
        $response->assertSee('No');
        $response->assertSee('ID');
        $response->assertSee('Search');
        $response->assertSee('Standard List Content');
        $response->assertSee('aria-label="View"', false);
        $response->assertSee('aria-label="Edit"', false);
    }

    public function test_content_list_shows_type_badges_for_mixed_content(): void
    {
        $admin = $this->admin();

        Content::create([
            'content_type' => 'page',
            'title' => 'About Page',
            'slug' => 'about-page',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        Content::create([
            'content_type' => 'article',
            'title' => 'News Article',
            'slug' => 'news-article',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/content');

        $response->assertOk();
        $response->assertSee('Page', false);
        $response->assertSee('Article', false);
        $response->assertSee('bi-file-earmark-text-fill', false);
        $response->assertSee('bi-journal-bookmark-fill', false);
    }

    public function test_content_list_renders_type_filter_dropdown(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin/content');

        $response->assertOk();
        $response->assertSee('name="content_type"', false);
        $response->assertSee('>Page</option>', false);
        $response->assertSee('>Article</option>', false);
        $response->assertSee('Apply');
    }

    public function test_content_list_filters_by_content_type_query(): void
    {
        $admin = $this->admin();

        Content::create([
            'content_type' => 'page',
            'title' => 'Filtered Page Item',
            'slug' => 'filtered-page-item',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        Content::create([
            'content_type' => 'article',
            'title' => 'Filtered Article Item',
            'slug' => 'filtered-article-item',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/content?content_type=page');

        $response->assertOk();
        $response->assertSee('Filtered Page Item');
        $response->assertDontSee('Filtered Article Item');
    }

    public function test_content_list_ignores_invalid_content_type_filter(): void
    {
        $admin = $this->admin();

        Content::create([
            'content_type' => 'page',
            'title' => 'Visible With Invalid Filter',
            'slug' => 'visible-with-invalid-filter',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/content?content_type=unknown');

        $response->assertOk();
        $response->assertSee('Visible With Invalid Filter');
    }

    public function test_create_from_filtered_list_prefills_content_type(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin/content/create?content_type=article');

        $response->assertOk();
        $response->assertSee('value="article"', false);
        $response->assertSee('selected', false);
    }

    public function test_content_list_searches_by_title_and_slug_and_sorts(): void
    {
        $admin = $this->admin();

        Content::create([
            'content_type' => 'page',
            'title' => 'Alpha Search Result',
            'slug' => 'alpha-search-result',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $admin->id,
        ]);

        Content::create([
            'content_type' => 'page',
            'title' => 'Beta Hidden Result',
            'slug' => 'beta-hidden-result',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/content?search=alpha-search&sort=title&direction=asc');

        $response->assertOk();
        $response->assertSee('Alpha Search Result');
        $response->assertDontSee('Beta Hidden Result');
        $response->assertSee('sort=title');
        $response->assertSee('direction=desc');
    }

    public function test_content_bulk_publish_updates_selected_content(): void
    {
        $admin = $this->admin();

        $content = Content::create([
            'content_type' => 'page',
            'title' => 'Bulk Publish Content',
            'slug' => 'bulk-publish-content',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        $this->actingAs($admin)->post('/admin/content/bulk', [
            'bulk_action' => 'publish',
            'selected' => [$content->id],
        ])->assertRedirect();

        $content->refresh();

        $this->assertEquals(ContentStatus::Published, $content->status);
        $this->assertNotNull($content->published_at);
    }

    public function test_media_picker_json_upload_accepts_multiple_files(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post('/admin/media', [
            'files' => [
                UploadedFile::fake()->create('picker-one.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('picker-two.jpg', 100, 'image/jpeg'),
            ],
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('uploaded_count', 2);
        $response->assertJsonCount(2, 'items');
        $this->assertDatabaseCount('media', 2);
    }
}

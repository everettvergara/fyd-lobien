<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\User;
use App\Modules\Media\Services\MediaService;
use App\Modules\Pages\Models\Page;
use App\Modules\Posts\Models\Post;
use App\Services\ContentSearchService;
use App\Services\PublishingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentFrameworkTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_service_uploads_and_lists_images(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $service = app(MediaService::class);

        $media = $service->upload(
            UploadedFile::fake()->image('hero.jpg'),
            null,
            'Hero image',
            $admin->id,
        );

        $this->assertDatabaseHas('media', ['id' => $media->id, 'alt_text' => 'Hero image']);
        Storage::disk('public')->assertExists($media->path);

        $pickerItems = $service->imagesForPicker();
        $this->assertTrue($pickerItems->contains(fn (array $item) => $item['id'] === $media->id));
    }

    public function test_publishing_service_publishes_content(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@fyd.local')->first();

        $page = Page::create([
            'title' => 'Draft Page',
            'slug' => 'draft-page',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        app(PublishingService::class)->publish($page, 'pages');

        $page->refresh();
        $this->assertEquals(ContentStatus::Published, $page->status);
        $this->assertNotNull($page->published_at);
    }

    public function test_publishing_service_duplicates_post_with_activity_log(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@fyd.local')->first();

        $post = Post::create([
            'title' => 'Original Post',
            'slug' => 'original-post',
            'status' => ContentStatus::Published,
            'author_id' => $admin->id,
            'published_at' => now(),
        ]);

        $duplicate = app(PublishingService::class)->duplicate($post, 'posts', [
            'title' => 'Original Post (Copy)',
            'slug' => 'original-post-copy',
            'author_id' => $admin->id,
        ]);

        $this->assertEquals(ContentStatus::Draft, $duplicate->status);
        $this->assertDatabaseHas('activity_log', [
            'module' => 'posts',
            'action' => 'created',
            'subject_id' => $duplicate->id,
        ]);
    }

    public function test_content_search_service_finds_published_content(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@fyd.local')->first();

        Page::create([
            'title' => 'Unique Searchable Page',
            'slug' => 'unique-searchable-page',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $admin->id,
            'content' => 'Laravel CMS framework content',
        ]);

        Post::create([
            'title' => 'Another Result',
            'slug' => 'another-result',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $admin->id,
            'content' => 'Unique Searchable Page mention',
        ]);

        $results = app(ContentSearchService::class)->search('Unique Searchable');

        $this->assertGreaterThanOrEqual(2, $results->count());
    }

    public function test_media_picker_endpoint_returns_json(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $media = app(MediaService::class)->upload(
            UploadedFile::fake()->image('picker.jpg'),
            null,
            null,
            $admin->id,
        );

        $response = $this->actingAs($admin)->getJson('/admin/media/picker');

        $response->assertOk();
        $response->assertJsonFragment(['id' => $media->id]);
    }
}

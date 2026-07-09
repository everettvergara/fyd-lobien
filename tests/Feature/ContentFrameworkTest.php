<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\User;
use App\Modules\ContentBlocks\Enums\ContentBlockFormatter;
use App\Modules\ContentBlocks\Models\ContentBlock;
use App\Modules\ContentBlocks\Services\ContentBlockRenderingService;
use App\Services\Media\MediaLibraryService;
use App\Services\Media\MediaUsageService;
use App\Modules\Media\Services\MediaService;
use App\Modules\Content\Models\Content;
use App\Services\ContentSearchService;
use App\Services\PublishingService;
use App\Support\PublicContent;
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
            UploadedFile::fake()->create('hero.jpg', 120, 'image/jpeg'),
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

        $content = Content::create([
            'content_type' => 'page',
            'title' => 'Draft Content',
            'slug' => 'draft-content',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        app(PublishingService::class)->publish($content, 'content');

        $content->refresh();
        $this->assertEquals(ContentStatus::Published, $content->status);
        $this->assertNotNull($content->published_at);
    }

    public function test_publishing_service_duplicates_content_with_activity_log(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@fyd.local')->first();

        $content = Content::create([
            'content_type' => 'article',
            'title' => 'Original Content',
            'slug' => 'original-content',
            'status' => ContentStatus::Published,
            'author_id' => $admin->id,
            'published_at' => now(),
        ]);

        $duplicate = app(PublishingService::class)->duplicate($content, 'content', [
            'title' => 'Original Content (Copy)',
            'slug' => 'original-content-copy',
            'author_id' => $admin->id,
        ]);

        $this->assertEquals(ContentStatus::Draft, $duplicate->status);
        $this->assertDatabaseHas('activity_log', [
            'module' => 'content',
            'action' => 'created',
            'subject_id' => $duplicate->id,
        ]);
    }

    public function test_content_search_service_finds_published_content(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@fyd.local')->first();

        Content::create([
            'content_type' => 'page',
            'title' => 'Unique Searchable Content',
            'slug' => 'unique-searchable-content',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $admin->id,
            'body' => 'Laravel CMS framework content',
        ]);

        Content::create([
            'content_type' => 'article',
            'title' => 'Another Result',
            'slug' => 'another-result',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $admin->id,
            'body' => 'Unique Searchable Content mention',
        ]);

        $results = app(ContentSearchService::class)->search('Unique Searchable');

        $this->assertGreaterThanOrEqual(2, $results->count());
    }

    public function test_media_store_endpoint_accepts_multiple_files(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $response = $this->actingAs($admin)->post('/admin/media', [
            'files' => [
                UploadedFile::fake()->create('one.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('two.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('three.jpg', 100, 'image/jpeg'),
            ],
            'alt_text' => 'Batch alt text',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('uploaded_count', 3);
        $response->assertJsonPath('requested_count', 3);
        $this->assertDatabaseCount('media', 3);
    }

    public function test_media_store_endpoint_accepts_single_file_field(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $response = $this->actingAs($admin)->post('/admin/media', [
            'file' => UploadedFile::fake()->create('single.jpg', 100, 'image/jpeg'),
            'alt_text' => 'Single alt text',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('uploaded_count', 1);
        $response->assertJsonPath('requested_count', 1);
        $this->assertDatabaseHas('media', [
            'original_filename' => 'single.jpg',
            'alt_text' => 'Single alt text',
        ]);
    }

    public function test_media_store_form_upload_accepts_multiple_files(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $response = $this->actingAs($admin)->post('/admin/media', [
            'files' => [
                UploadedFile::fake()->create('form-one.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('form-two.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('form-three.jpg', 100, 'image/jpeg'),
            ],
            'alt_text' => 'Form alt text',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', '3 files uploaded successfully.');
        $this->assertDatabaseCount('media', 3);
    }

    public function test_media_store_form_upload_saves_valid_files_when_one_file_fails(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $failedPath = tempnam(sys_get_temp_dir(), 'failed-upload');
        file_put_contents($failedPath, 'failed');

        $response = $this->actingAs($admin)->post('/admin/media', [
            'files' => [
                UploadedFile::fake()->create('valid-one.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('valid-two.jpg', 100, 'image/jpeg'),
                new UploadedFile($failedPath, 'too-large.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true),
            ],
            'alt_text' => 'Partial upload alt text',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning', '2 files uploaded; 1 file failed.');
        $response->assertSessionHasErrors('files.2');
        $this->assertDatabaseCount('media', 2);
        $this->assertDatabaseHas('media', ['original_filename' => 'valid-one.jpg']);
        $this->assertDatabaseHas('media', ['original_filename' => 'valid-two.jpg']);
    }

    public function test_media_picker_endpoint_returns_json(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $media = app(MediaService::class)->upload(
            UploadedFile::fake()->create('picker.jpg', 120, 'image/jpeg'),
            null,
            null,
            $admin->id,
        );

        $response = $this->actingAs($admin)->getJson('/admin/media/picker');

        $response->assertOk();
        $response->assertJsonFragment(['id' => $media->id]);
    }

    public function test_media_library_searches_metadata_and_tags(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $library = app(MediaLibraryService::class);

        $media = $library->upload(
            UploadedFile::fake()->create('annual-report.pdf', 64, 'application/pdf'),
            [
                'title' => 'Annual Impact Report',
                'description' => 'FYD community outcomes',
                'tags' => 'reports, impact',
            ],
            $admin->id,
        );

        $results = $library->browse(['search' => 'impact'], 10);

        $this->assertTrue($results->getCollection()->contains(fn (Media $item) => $item->id === $media->id));
        $this->assertDatabaseHas('media_tags', ['slug' => 'impact']);
        $this->assertDatabaseHas('media_variants', ['media_id' => $media->id, 'variant' => 'original']);
    }

    public function test_media_deletion_is_blocked_when_asset_is_in_use(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $library = app(MediaLibraryService::class);
        $media = $library->upload(
            UploadedFile::fake()->create('hero.jpg', 64, 'image/jpeg'),
            [],
            $admin->id,
        );
        $content = Content::create([
            'content_type' => 'page',
            'title' => 'Media Usage Content',
            'slug' => 'media-usage-content',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
            'featured_image_id' => $media->id,
        ]);

        app(MediaUsageService::class)->syncModel($content, 'content', [
            'featured_image_id' => 'Featured Image',
        ]);

        $this->expectException(\RuntimeException::class);

        $library->deletion->softDelete($media);
    }

    public function test_admin_can_store_content_with_url_link_and_pdf_attachment(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $pdf = app(MediaLibraryService::class)->upload(
            UploadedFile::fake()->create('report.pdf', 64, 'application/pdf'),
            ['title' => 'Annual Report'],
            $admin->id,
        );

        $response = $this->actingAs($admin)->post('/admin/content', [
            'content_type' => 'article',
            'title' => 'Content With Attachment',
            'slug' => 'content-with-attachment',
            'status' => ContentStatus::Draft->value,
            'url_link' => 'https://example.com/reference',
            'attachment_id' => $pdf->id,
        ]);

        $response->assertRedirect('/admin/content');

        $this->assertDatabaseHas('contents', [
            'slug' => 'content-with-attachment',
            'url_link' => 'https://example.com/reference',
            'attachment_id' => $pdf->id,
        ]);
    }

    public function test_admin_rejects_non_pdf_attachment(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $image = app(MediaLibraryService::class)->upload(
            UploadedFile::fake()->create('photo.jpg', 64, 'image/jpeg'),
            [],
            $admin->id,
        );

        $response = $this->actingAs($admin)->post('/admin/content', [
            'content_type' => 'article',
            'title' => 'Invalid Attachment Content',
            'slug' => 'invalid-attachment-content',
            'status' => ContentStatus::Draft->value,
            'attachment_id' => $image->id,
        ]);

        $response->assertSessionHasErrors('attachment_id');
        $this->assertDatabaseMissing('contents', ['slug' => 'invalid-attachment-content']);
    }

    public function test_public_content_entry_includes_url_link_and_attachment(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $pdf = app(MediaLibraryService::class)->upload(
            UploadedFile::fake()->create('guide.pdf', 64, 'application/pdf'),
            ['title' => 'User Guide'],
            $admin->id,
        );

        $content = Content::create([
            'content_type' => 'article',
            'title' => 'Public Entry Content',
            'slug' => 'public-entry-content',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $admin->id,
            'url_link' => 'https://example.com/guide',
            'attachment_id' => $pdf->id,
        ]);

        $entry = PublicContent::entry($content);

        $this->assertSame('https://example.com/guide', $entry['urlLink']);
        $this->assertSame('User Guide', $entry['attachment']['label']);
        $this->assertSame('application/pdf', $entry['attachment']['mimeType']);
        $this->assertNotEmpty($entry['attachment']['url']);
        $this->assertSame([], $entry['galleryImages']);
    }

    public function test_public_content_entry_includes_gallery_images(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $firstId = app(MediaLibraryService::class)->upload(
            UploadedFile::fake()->create('gallery-one.jpg', 64, 'image/jpeg'),
            [],
            $admin->id,
        )->id;
        $secondId = app(MediaLibraryService::class)->upload(
            UploadedFile::fake()->create('gallery-two.jpg', 64, 'image/jpeg'),
            [],
            $admin->id,
        )->id;

        $content = Content::create([
            'content_type' => 'article',
            'title' => 'Gallery Entry Content',
            'slug' => 'gallery-entry-content',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $admin->id,
            'featured_image_id' => $firstId,
        ]);
        $content->galleryImages()->sync([
            $firstId => ['sort_order' => 0],
            $secondId => ['sort_order' => 1],
        ]);

        $entry = PublicContent::entry($content->fresh(['galleryImages']));

        $this->assertCount(2, $entry['galleryImages']);
        $this->assertNotEmpty($entry['galleryImages'][0]['url']);
        $this->assertNotEmpty($entry['galleryImages'][1]['url']);
    }

    public function test_content_block_renders_url_link_and_attachment_fields(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $pdf = app(MediaLibraryService::class)->upload(
            UploadedFile::fake()->create('resource.pdf', 64, 'application/pdf'),
            ['title' => 'Resource PDF'],
            $admin->id,
        );

        Content::create([
            'content_type' => 'article',
            'title' => 'Block Field Content',
            'slug' => 'block-field-content',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $admin->id,
            'url_link' => 'https://example.com/resource',
            'attachment_id' => $pdf->id,
        ]);

        $block = ContentBlock::create([
            'name' => 'Attachment Fields Block',
            'key' => 'attachment-fields-block',
            'status' => ContentStatus::Published,
            'content_types' => ['article'],
            'fields' => [
                ['field' => 'url_link', 'label' => 'URL', 'class' => 'content-block__url-link', 'id' => 'content-block-attachment-fields-block-url-link', 'sort_order' => 0],
                ['field' => 'attachment', 'label' => 'PDF', 'class' => 'content-block__attachment', 'id' => 'content-block-attachment-fields-block-attachment', 'sort_order' => 1],
            ],
            'filters' => [
                ['field' => 'title', 'operator' => 'contains', 'value' => 'Block Field Content', 'group' => 'and'],
            ],
            'sort_field' => 'published_at',
            'sort_direction' => 'desc',
            'items_per_page' => 10,
            'pagination_enabled' => false,
            'formatter' => ContentBlockFormatter::Unformatted,
        ]);

        $dto = app(ContentBlockRenderingService::class)->dto($block);

        $this->assertCount(1, $dto['rows']);
        $this->assertSame('https://example.com/resource', $dto['rows'][0][0]['value']);
        $this->assertSame('Resource PDF', $dto['rows'][0][1]['value']['label']);
        $this->assertSame('application/pdf', $dto['rows'][0][1]['value']['mimeType']);
    }
}

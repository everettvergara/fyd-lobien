<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\MediaUsage;
use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Services\Media\MediaLibraryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentGalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        $this->seed();

        return User::where('email', 'admin@fyd.local')->first();
    }

    protected function uploadImage(string $filename = 'gallery.jpg'): int
    {
        Storage::fake('public');

        $media = app(MediaLibraryService::class)->upload(
            UploadedFile::fake()->create($filename, 64, 'image/jpeg'),
            [],
            $this->admin()->id,
        );

        return $media->id;
    }

    public function test_store_syncs_gallery_and_sets_featured_image_from_first_item(): void
    {
        $admin = $this->admin();
        $firstId = $this->uploadImage('first.jpg');
        $secondId = $this->uploadImage('second.jpg');

        $response = $this->actingAs($admin)->post('/admin/content', [
            'content_type' => 'page',
            'title' => 'Gallery Page',
            'slug' => 'gallery-page',
            'status' => ContentStatus::Draft->value,
            'gallery_media_ids' => [$firstId, $secondId],
        ]);

        $response->assertRedirect('/admin/content');

        $content = Content::where('slug', 'gallery-page')->first();
        $this->assertNotNull($content);
        $this->assertSame($firstId, $content->featured_image_id);
        $this->assertSame([$firstId, $secondId], $content->galleryImages()->pluck('media.id')->all());
        $this->assertDatabaseHas('content_media', [
            'content_id' => $content->id,
            'media_id' => $firstId,
            'sort_order' => 0,
        ]);
        $this->assertDatabaseHas('content_media', [
            'content_id' => $content->id,
            'media_id' => $secondId,
            'sort_order' => 1,
        ]);
    }

    public function test_update_replaces_gallery_and_updates_featured_image(): void
    {
        $admin = $this->admin();
        $originalFirstId = $this->uploadImage('original-first.jpg');
        $originalSecondId = $this->uploadImage('original-second.jpg');
        $replacementId = $this->uploadImage('replacement.jpg');

        $content = Content::create([
            'content_type' => 'page',
            'title' => 'Existing Gallery Page',
            'slug' => 'existing-gallery-page',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
            'featured_image_id' => $originalFirstId,
        ]);
        $content->galleryImages()->sync([
            $originalFirstId => ['sort_order' => 0],
            $originalSecondId => ['sort_order' => 1],
        ]);

        $response = $this->actingAs($admin)->put("/admin/content/{$content->id}", [
            'content_type' => 'page',
            'title' => 'Existing Gallery Page',
            'slug' => 'existing-gallery-page',
            'status' => ContentStatus::Draft->value,
            'gallery_media_ids' => [$replacementId],
        ]);

        $response->assertRedirect('/admin/content');

        $content->refresh();
        $this->assertSame($replacementId, $content->featured_image_id);
        $this->assertSame([$replacementId], $content->galleryImages()->pluck('media.id')->all());
        $this->assertDatabaseMissing('content_media', [
            'content_id' => $content->id,
            'media_id' => $originalFirstId,
        ]);
        $this->assertDatabaseMissing('content_media', [
            'content_id' => $content->id,
            'media_id' => $originalSecondId,
        ]);
    }

    public function test_gallery_sync_registers_media_usage_for_each_image(): void
    {
        $admin = $this->admin();
        $firstId = $this->uploadImage('usage-first.jpg');
        $secondId = $this->uploadImage('usage-second.jpg');

        $this->actingAs($admin)->post('/admin/content', [
            'content_type' => 'article',
            'title' => 'Usage Gallery Article',
            'slug' => 'usage-gallery-article',
            'status' => ContentStatus::Draft->value,
            'gallery_media_ids' => [$firstId, $secondId],
        ])->assertRedirect('/admin/content');

        $content = Content::where('slug', 'usage-gallery-article')->first();
        $this->assertNotNull($content);

        $this->assertDatabaseHas('media_usage', [
            'media_id' => $firstId,
            'usable_type' => Content::class,
            'usable_id' => $content->id,
            'module' => 'content',
            'field' => 'featured_image_id',
        ]);
        $this->assertDatabaseHas('media_usage', [
            'media_id' => $firstId,
            'usable_type' => Content::class,
            'usable_id' => $content->id,
            'module' => 'content',
            'field' => 'gallery_media_'.$firstId,
        ]);
        $this->assertDatabaseHas('media_usage', [
            'media_id' => $secondId,
            'usable_type' => Content::class,
            'usable_id' => $content->id,
            'module' => 'content',
            'field' => 'gallery_media_'.$secondId,
        ]);
        $this->assertSame(3, MediaUsage::where('usable_type', Content::class)->where('usable_id', $content->id)->count());
    }
}

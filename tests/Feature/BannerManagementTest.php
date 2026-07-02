<?php

namespace Tests\Feature;

use App\Enums\BannerType;
use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\User;
use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Models\BannerTemplate;
use App\Modules\Banners\Services\BannerRenderingService;
use App\Modules\Media\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        $this->seed();

        return User::where('email', 'admin@fyd.local')->firstOrFail();
    }

    public function test_removed_templates_are_not_seeded(): void
    {
        $this->admin();

        $this->assertDatabaseMissing('banner_templates', ['key' => 'hero_carousel']);
        $this->assertDatabaseMissing('banner_templates', ['key' => 'fullscreen_hero']);
        $this->assertDatabaseMissing('banner_templates', ['key' => 'card_overlay']);
    }

    public function test_admin_creates_normalized_banner_content(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $media = app(MediaService::class)->upload(
            UploadedFile::fake()->create('desktop.jpg', 120, 'image/jpeg'),
            null,
            'Desktop alt',
            $admin->id,
        );

        $response = $this->actingAs($admin)->post('/admin/banners', [
            'name' => 'Campaign Hero',
            'key' => 'campaign-hero',
            'template_id' => BannerTemplate::where('key', 'hero_left')->value('id'),
            'slides' => [[
                'name' => 'Default',
                'blocks' => [[
                    'region' => 'main',
                    'type' => 'content',
                    'headline' => 'Campaign Headline',
                    'subheading' => 'Campaign',
                    'description' => 'Reusable banner content.',
                    'buttons' => [[
                        'label' => 'Learn More',
                        'url' => '/campaign',
                        'target' => '_self',
                        'style' => 'primary',
                    ]],
                ]],
                'media' => [
                    'desktop_image' => ['media_id' => $media->id],
                ],
            ]],
            'status' => ContentStatus::Published->value,
            'effect' => 'fade',
            'animation_speed' => 500,
            'delay' => 0,
            'loop' => 0,
            'autoplay' => 0,
        ]);

        $response->assertRedirect('/admin/banners');

        $banner = Banner::where('key', 'campaign-hero')->firstOrFail();
        $this->assertDatabaseHas('banner_slides', ['banner_id' => $banner->id]);
        $this->assertDatabaseHas('banner_content_blocks', ['banner_id' => $banner->id, 'headline' => 'Campaign Headline']);
        $this->assertDatabaseHas('banner_buttons', ['label' => 'Learn More', 'url' => '/campaign']);
        $this->assertDatabaseHas('banner_media_assignments', ['banner_id' => $banner->id, 'slot' => 'desktop_image', 'media_id' => $media->id]);
        $this->assertDatabaseHas('media_usage', ['media_id' => $media->id, 'usable_type' => Banner::class, 'usable_id' => $banner->id]);
    }

    public function test_image_carousel_template_is_seeded(): void
    {
        $this->admin();

        $this->assertDatabaseHas('banner_templates', ['key' => 'image_carousel']);
    }

    public function test_inner_page_template_is_seeded(): void
    {
        $this->admin();

        $this->assertDatabaseHas('banner_templates', ['key' => 'inner_page']);

        $template = BannerTemplate::where('key', 'inner_page')->firstOrFail();
        $this->assertContains('background_image', $template->schema['mediaSlots'] ?? []);
    }

    public function test_admin_creates_inner_page_banner_without_ctas(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post('/admin/banners', [
            'name' => 'About Page Banner',
            'key' => 'page-about-test',
            'template_id' => BannerTemplate::where('key', 'inner_page')->value('id'),
            'slides' => [[
                'name' => 'Default',
                'blocks' => [[
                    'region' => 'main',
                    'type' => 'content',
                    'headline' => 'About Us',
                    'subheading' => 'Our Story',
                    'description' => 'Learn about our mission and team.',
                    'buttons' => [],
                ]],
                'media' => [],
            ]],
            'status' => ContentStatus::Published->value,
            'effect' => 'none',
            'animation_speed' => 500,
            'delay' => 0,
            'loop' => 0,
            'autoplay' => 0,
        ]);

        $response->assertRedirect('/admin/banners');

        $banner = Banner::where('key', 'page-about-test')->firstOrFail();
        $this->assertSame('About Us', $banner->title);
        $block = $banner->slides()->first()->contentBlocks()->first();
        $this->assertSame(0, $block->buttons()->count());
    }

    public function test_admin_creates_inner_page_banner_with_optional_background_image(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $media = app(MediaService::class)->upload(
            UploadedFile::fake()->create('page-header.jpg', 120, 'image/jpeg'),
            null,
            'Page header background',
            $admin->id,
        );

        $response = $this->actingAs($admin)->post('/admin/banners', [
            'name' => 'Services Page Banner',
            'key' => 'page-services-test',
            'template_id' => BannerTemplate::where('key', 'inner_page')->value('id'),
            'slides' => [[
                'name' => 'Default',
                'blocks' => [[
                    'region' => 'main',
                    'type' => 'content',
                    'headline' => 'Services',
                    'subheading' => 'What We Offer',
                    'description' => 'Explore our professional services.',
                    'buttons' => [],
                ]],
                'media' => [
                    'background_image' => ['media_id' => $media->id],
                ],
            ]],
            'status' => ContentStatus::Published->value,
            'effect' => 'none',
            'animation_speed' => 500,
            'delay' => 0,
            'loop' => 0,
            'autoplay' => 0,
        ]);

        $response->assertRedirect('/admin/banners');

        $banner = Banner::where('key', 'page-services-test')->firstOrFail();
        $this->assertDatabaseHas('banner_media_assignments', [
            'banner_id' => $banner->id,
            'slot' => 'background_image',
            'media_id' => $media->id,
        ]);

        $payload = app(BannerRenderingService::class)->bannerByKey('page-services-test');
        $this->assertNotNull($payload['backgroundImage']);
        $this->assertSame($media->id, $payload['backgroundImage']['id'] ?? null);
    }

    public function test_admin_creates_carousel_banner_with_multiple_slides(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post('/admin/banners', [
            'name' => 'Homepage Carousel',
            'key' => 'homepage-carousel',
            'template_id' => BannerTemplate::where('key', 'image_carousel')->value('id'),
            'slides' => [
                [
                    'name' => 'Slide 1',
                    'blocks' => [[
                        'region' => 'main',
                        'type' => 'content',
                        'headline' => 'First Slide',
                        'subheading' => 'One',
                        'description' => 'First carousel slide.',
                        'buttons' => [[
                            'label' => 'Go',
                            'url' => '/one',
                            'target' => '_self',
                            'style' => 'primary',
                        ]],
                    ]],
                    'media' => [],
                ],
                [
                    'name' => 'Slide 2',
                    'blocks' => [[
                        'region' => 'main',
                        'type' => 'content',
                        'headline' => 'Second Slide',
                        'subheading' => 'Two',
                        'description' => 'Second carousel slide.',
                        'buttons' => [[
                            'label' => 'Next',
                            'url' => '/two',
                            'target' => '_self',
                            'style' => 'secondary',
                        ]],
                    ]],
                    'media' => [],
                ],
                [
                    'name' => 'Empty Slide',
                    'blocks' => [[
                        'region' => 'main',
                        'type' => 'content',
                        'headline' => null,
                        'subheading' => null,
                        'description' => null,
                        'buttons' => [[
                            'label' => null,
                            'url' => null,
                            'target' => '_self',
                            'style' => 'primary',
                        ]],
                    ]],
                    'media' => [],
                ],
            ],
            'status' => ContentStatus::Published->value,
            'effect' => 'slide',
            'animation_speed' => 500,
            'delay' => 0,
            'loop' => 1,
            'autoplay' => 1,
        ]);

        $response->assertRedirect('/admin/banners');

        $banner = Banner::where('key', 'homepage-carousel')->firstOrFail();
        $this->assertSame(BannerType::Carousel, $banner->type);
        $this->assertSame(2, $banner->slides()->count());
        $this->assertDatabaseHas('banner_content_blocks', ['banner_id' => $banner->id, 'headline' => 'First Slide']);
        $this->assertDatabaseHas('banner_content_blocks', ['banner_id' => $banner->id, 'headline' => 'Second Slide']);
    }

    public function test_edit_form_shows_column_fields_when_template_switched(): void
    {
        $admin = $this->admin();
        $twoColumnId = BannerTemplate::where('key', 'two_column_full_width')->value('id');
        $banner = Banner::firstOrFail();

        $response = $this->actingAs($admin)->get("/admin/banners/{$banner->id}/edit?template_id={$twoColumnId}");

        $response->assertOk();
        $response->assertSee('Column 1 Picture', false);
        $response->assertSee('Column 2 Picture', false);
        $response->assertSee('Column 1', false);
        $response->assertSee('Column 2', false);
    }

    public function test_admin_creates_two_column_banner_with_full_column_fields(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $columnOneMedia = app(MediaService::class)->upload(
            UploadedFile::fake()->create('column-one.jpg', 120, 'image/jpeg'),
            null,
            'Column one',
            $admin->id,
        );
        $columnTwoMedia = app(MediaService::class)->upload(
            UploadedFile::fake()->create('column-two.jpg', 120, 'image/jpeg'),
            null,
            'Column two',
            $admin->id,
        );

        $response = $this->actingAs($admin)->post('/admin/banners', [
            'name' => 'Two Column Banner',
            'key' => 'two-column-banner',
            'template_id' => BannerTemplate::where('key', 'two_column_full_width')->value('id'),
            'slides' => [[
                'name' => 'Default',
                'blocks' => [
                    [
                        'region' => 'column_1',
                        'type' => 'content',
                        'headline' => 'Column One Title',
                        'subheading' => 'Column One Subtitle',
                        'description' => 'Column one text.',
                        'buttons' => [[
                            'label' => 'Column One CTA',
                            'url' => '/one',
                            'target' => '_self',
                            'style' => 'primary',
                        ]],
                    ],
                    [
                        'region' => 'column_2',
                        'type' => 'content',
                        'headline' => 'Column Two Title',
                        'subheading' => 'Column Two Subtitle',
                        'description' => 'Column two text.',
                        'buttons' => [[
                            'label' => 'Column Two CTA',
                            'url' => '/two',
                            'target' => '_self',
                            'style' => 'secondary',
                        ]],
                    ],
                ],
                'media' => [
                    'column_1_image' => ['media_id' => $columnOneMedia->id],
                    'column_2_image' => ['media_id' => $columnTwoMedia->id],
                ],
            ]],
            'status' => ContentStatus::Published->value,
            'effect' => 'none',
            'animation_speed' => 500,
            'delay' => 0,
            'loop' => 0,
            'autoplay' => 0,
        ]);

        $response->assertRedirect('/admin/banners');

        $banner = Banner::where('key', 'two-column-banner')->firstOrFail();
        $this->assertDatabaseHas('banner_content_blocks', ['banner_id' => $banner->id, 'region' => 'column_1', 'headline' => 'Column One Title', 'description' => 'Column one text.']);
        $this->assertDatabaseHas('banner_content_blocks', ['banner_id' => $banner->id, 'region' => 'column_2', 'headline' => 'Column Two Title', 'description' => 'Column two text.']);
        $this->assertDatabaseHas('banner_buttons', ['label' => 'Column One CTA', 'url' => '/one']);
        $this->assertDatabaseHas('banner_buttons', ['label' => 'Column Two CTA', 'url' => '/two']);
        $this->assertDatabaseHas('banner_media_assignments', ['banner_id' => $banner->id, 'slot' => 'column_1_image', 'media_id' => $columnOneMedia->id]);
        $this->assertDatabaseHas('banner_media_assignments', ['banner_id' => $banner->id, 'slot' => 'column_2_image', 'media_id' => $columnTwoMedia->id]);
    }

    public function test_admin_creates_banner_with_rich_text_content(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post('/admin/banners', [
            'name' => 'Rich Text Hero',
            'key' => 'rich-text-hero',
            'template_id' => BannerTemplate::where('key', 'hero_center')->value('id'),
            'slides' => [[
                'name' => 'Default',
                'blocks' => [[
                    'region' => 'main',
                    'type' => 'content',
                    'headline' => 'Rich Headline',
                    'subheading' => 'Rich Subheading',
                    'description' => 'Summary text.',
                    'rich_text' => '<p><strong>Formatted</strong> body copy.</p>',
                    'buttons' => [[
                        'label' => 'Go',
                        'url' => '/go',
                        'target' => '_self',
                        'style' => 'primary',
                    ]],
                ]],
                'media' => [],
            ]],
            'status' => ContentStatus::Published->value,
            'effect' => 'none',
            'animation_speed' => 500,
            'delay' => 0,
            'loop' => 0,
            'autoplay' => 0,
        ]);

        $response->assertRedirect('/admin/banners');

        $banner = Banner::where('key', 'rich-text-hero')->firstOrFail();
        $this->assertDatabaseHas('banner_content_blocks', [
            'banner_id' => $banner->id,
            'rich_text' => '<p><strong>Formatted</strong> body copy.</p>',
        ]);

        $payload = app(BannerRenderingService::class)->dto($banner->fresh(['template', 'slides.contentBlocks.buttons', 'slides.mediaAssignments.media']));
        $this->assertSame('<p><strong>Formatted</strong> body copy.</p>', $payload['slides'][0]['blocks'][0]['richText']);
    }

    public function test_public_rendering_returns_active_banner_by_key(): void
    {
        $admin = $this->admin();
        $template = BannerTemplate::where('key', 'hero_center')->firstOrFail();

        $banner = Banner::create([
            'name' => 'Landing Hero',
            'key' => 'landing-hero',
            'title' => 'Landing Headline',
            'type' => 'hero',
            'template_id' => $template->id,
            'status' => ContentStatus::Published,
        ]);

        app(\App\Modules\Banners\Services\BannerService::class)->syncStructure($banner, [
            'title' => 'Landing Headline',
            'subtitle' => 'Landing',
            'description' => 'Key rendered.',
            'button_text' => 'Start',
            'button_url' => '/start',
        ]);

        $payload = app(BannerRenderingService::class)->bannerByKey('landing-hero');

        $this->assertSame('Landing Headline', $payload['title']);
        $this->assertSame('hero_center', $payload['template']['key']);
        $this->assertSame('Start', $payload['buttonText']);
    }

    public function test_draft_banner_is_not_returned_by_key(): void
    {
        $template = BannerTemplate::where('key', 'hero_center')->firstOrFail();

        Banner::create([
            'name' => 'Draft Hero',
            'key' => 'draft-hero',
            'title' => 'Draft Headline',
            'type' => 'hero',
            'template_id' => $template->id,
            'status' => ContentStatus::Draft,
        ]);

        $this->assertNull(app(BannerRenderingService::class)->bannerByKey('draft-hero'));
    }

    public function test_bulk_archive_updates_selected_banners(): void
    {
        $admin = $this->admin();
        $banner = Banner::firstOrFail();

        $response = $this->actingAs($admin)->post('/admin/banners/bulk', [
            'bulk_action' => 'archive',
            'selected' => [$banner->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'status' => ContentStatus::Archived->value,
        ]);
    }
}

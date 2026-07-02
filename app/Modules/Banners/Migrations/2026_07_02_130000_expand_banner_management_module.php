<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banner_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('category')->default('hero');
            $table->json('schema')->nullable();
            $table->json('default_settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('banner_placements', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->foreignId('template_id')->nullable()->after('type')->constrained('banner_templates')->nullOnDelete();
            $table->text('internal_notes')->nullable()->after('description');
            $table->string('visibility')->default('published')->after('status');
            $table->string('timezone', 80)->default('UTC')->after('expires_at');
            $table->json('settings')->nullable()->after('timezone');
            $table->json('effect_settings')->nullable()->after('settings');

            $table->index(['placement', 'sort_order']);
            $table->index(['status', 'published_at', 'expires_at']);
            $table->index(['template_id', 'updated_at']);
            $table->index(['visibility', 'updated_at']);
        });

        Schema::create('banner_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id')->constrained('banners')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['banner_id', 'sort_order']);
        });

        Schema::create('banner_content_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id')->constrained('banners')->cascadeOnDelete();
            $table->foreignId('banner_slide_id')->nullable()->constrained('banner_slides')->cascadeOnDelete();
            $table->string('region')->default('main');
            $table->string('type')->default('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('headline')->nullable();
            $table->string('subheading')->nullable();
            $table->text('description')->nullable();
            $table->text('rich_text')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['banner_id', 'sort_order']);
            $table->index(['banner_slide_id', 'region', 'sort_order'], 'banner_blocks_slide_region_sort_index');
        });

        Schema::create('banner_buttons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_content_block_id')->constrained('banner_content_blocks')->cascadeOnDelete();
            $table->string('label');
            $table->string('url', 500);
            $table->string('target', 20)->default('_self');
            $table->string('style', 50)->default('primary');
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['banner_content_block_id', 'sort_order'], 'banner_buttons_block_sort_index');
        });

        Schema::create('banner_media_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id')->constrained('banners')->cascadeOnDelete();
            $table->foreignId('banner_slide_id')->nullable()->constrained('banner_slides')->cascadeOnDelete();
            $table->foreignId('banner_content_block_id')->nullable()->constrained('banner_content_blocks')->cascadeOnDelete();
            $table->string('slot', 80);
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('alt_text')->nullable();
            $table->string('title_attribute')->nullable();
            $table->string('aria_label')->nullable();
            $table->text('caption')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['banner_id', 'slot']);
            $table->index(['banner_slide_id', 'slot'], 'banner_media_slide_slot_index');
        });

        $this->seedTemplates();
        $this->seedPlacements();
        $this->migrateExistingBanners();
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_media_assignments');
        Schema::dropIfExists('banner_buttons');
        Schema::dropIfExists('banner_content_blocks');
        Schema::dropIfExists('banner_slides');

        Schema::table('banners', function (Blueprint $table) {
            $table->dropIndex(['placement', 'sort_order']);
            $table->dropIndex(['status', 'published_at', 'expires_at']);
            $table->dropIndex(['template_id', 'updated_at']);
            $table->dropIndex(['visibility', 'updated_at']);
            $table->dropConstrainedForeignId('template_id');
            $table->dropColumn([
                'slug',
                'internal_notes',
                'visibility',
                'timezone',
                'settings',
                'effect_settings',
            ]);
        });

        Schema::dropIfExists('banner_placements');
        Schema::dropIfExists('banner_templates');
    }

    protected function seedTemplates(): void
    {
        $templates = [
            ['hero_center', 'Hero Center', 'hero', ['alignment' => 'center', 'max_blocks' => 1]],
            ['hero_left', 'Hero Left', 'hero', ['alignment' => 'left', 'max_blocks' => 1]],
            ['hero_right', 'Hero Right', 'hero', ['alignment' => 'right', 'max_blocks' => 1]],
            ['fullscreen_hero', 'Fullscreen Hero', 'hero', ['height' => 'fullscreen']],
            ['split_layout', 'Split Layout', 'layout', ['columns' => 2, 'ratio' => '50/50']],
            ['video_hero', 'Video Hero', 'video', ['requires_video' => true]],
            ['minimal', 'Minimal', 'hero', ['minimal' => true]],
            ['card_overlay', 'Card Overlay', 'overlay', ['overlay' => 'card']],
            ['image_left', 'Image Left', 'layout', ['media_position' => 'left']],
            ['image_right', 'Image Right', 'layout', ['media_position' => 'right']],
            ['two_column_full_width', 'Two-Column Full Width', 'layout', ['columns' => 2, 'ratios' => ['50/50', '40/60', '60/40']]],
            ['three_column_full_width', 'Three-Column Full Width', 'layout', ['columns' => 3, 'stack_mobile' => true]],
        ];

        foreach ($templates as $index => [$key, $name, $category, $schema]) {
            DB::table('banner_templates')->updateOrInsert(
                ['key' => $key],
                [
                    'name' => $name,
                    'description' => $name.' reusable banner template.',
                    'category' => $category,
                    'schema' => json_encode($schema),
                    'default_settings' => json_encode(['container' => 'full_width']),
                    'is_active' => true,
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    protected function seedPlacements(): void
    {
        $placements = [
            ['homepage_hero', 'Homepage Hero'],
            ['homepage_secondary', 'Homepage Secondary'],
            ['homepage_cta', 'Homepage CTA'],
            ['homepage_slider', 'Homepage Slider'],
            ['landing_hero', 'Landing Hero'],
            ['product_hero', 'Product Hero'],
            ['sidebar', 'Sidebar'],
            ['footer', 'Footer'],
            ['section_banner', 'Section Banner'],
        ];

        foreach ($placements as $index => [$key, $name]) {
            DB::table('banner_placements')->updateOrInsert(
                ['key' => $key],
                [
                    'name' => $name,
                    'description' => $name.' placement.',
                    'is_system' => true,
                    'is_active' => true,
                    'sort_order' => $index,
                    'settings' => json_encode([]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    protected function migrateExistingBanners(): void
    {
        $templateIds = DB::table('banner_templates')->pluck('id', 'key');

        DB::table('banners')->orderBy('id')->get()->each(function ($banner) use ($templateIds) {
            $templateKey = $banner->type === 'carousel' ? 'hero_center' : match ($banner->type) {
                'promotional' => 'card_overlay',
                'landing' => 'hero_left',
                default => 'hero_center',
            };

            DB::table('banners')->where('id', $banner->id)->update([
                'slug' => Str::slug($banner->name).'-'.$banner->id,
                'template_id' => $templateIds[$templateKey] ?? null,
                'visibility' => $banner->status === 'archived' ? 'archived' : 'published',
                'timezone' => 'UTC',
                'settings' => json_encode([]),
                'effect_settings' => json_encode(['effect' => 'fade', 'speed' => 500, 'delay' => 0, 'loop' => false, 'autoplay' => false]),
            ]);

            $slideId = DB::table('banner_slides')->insertGetId([
                'banner_id' => $banner->id,
                'name' => 'Default',
                'sort_order' => 0,
                'settings' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $blockId = DB::table('banner_content_blocks')->insertGetId([
                'banner_id' => $banner->id,
                'banner_slide_id' => $slideId,
                'region' => 'main',
                'type' => 'content',
                'sort_order' => 0,
                'headline' => $banner->title,
                'subheading' => $banner->subtitle,
                'description' => $banner->description,
                'rich_text' => null,
                'settings' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($banner->button_text && $banner->button_url) {
                DB::table('banner_buttons')->insert([
                    'banner_content_block_id' => $blockId,
                    'label' => $banner->button_text,
                    'url' => $banner->button_url,
                    'target' => '_self',
                    'style' => 'primary',
                    'sort_order' => 0,
                    'settings' => json_encode([]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ([
                'desktop_image_id' => 'desktop_image',
                'mobile_image_id' => 'mobile_image',
                'background_image_id' => 'background_image',
            ] as $field => $slot) {
                if (! $banner->{$field}) {
                    continue;
                }

                DB::table('banner_media_assignments')->insert([
                    'banner_id' => $banner->id,
                    'banner_slide_id' => $slideId,
                    'banner_content_block_id' => null,
                    'slot' => $slot,
                    'media_id' => $banner->{$field},
                    'sort_order' => 0,
                    'settings' => json_encode([]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
};

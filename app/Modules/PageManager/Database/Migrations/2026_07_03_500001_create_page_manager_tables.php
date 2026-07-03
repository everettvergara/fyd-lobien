<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_masters', function (Blueprint $table) {
            $table->id();
            $table->string('default_seo_title_suffix')->nullable();
            $table->string('default_robots')->default('index,follow');
            $table->string('default_sitemap_changefreq')->default('monthly');
            $table->decimal('default_sitemap_priority', 2, 1)->default(0.5);
            $table->boolean('is_configured')->default(false);
            $table->timestamps();
        });

        Schema::create('page_master_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_master_id')->constrained('page_masters')->cascadeOnDelete();
            $table->string('region_key');
            $table->string('block_type');
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index(['page_master_id', 'region_key', 'sort_order']);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->string('slug');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index('slug');
        });

        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('region_key');
            $table->string('block_type');
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index(['page_id', 'region_key', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_master_blocks');
        Schema::dropIfExists('page_masters');
    }
};

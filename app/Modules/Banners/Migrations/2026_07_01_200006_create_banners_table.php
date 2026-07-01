<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->default('hero');
            $table->string('placement')->default('homepage_hero');
            $table->foreignId('desktop_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('mobile_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('background_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('button_text')->nullable();
            $table->string('button_url', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};

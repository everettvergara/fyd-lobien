<?php

use App\Enums\ContentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('status')->default('draft');
            $table->json('content_types');
            $table->json('fields');
            $table->json('filters')->nullable();
            $table->string('sort_field')->default('published_at');
            $table->string('sort_direction', 4)->default('desc');
            $table->unsignedInteger('items_per_page')->default(10);
            $table->boolean('pagination_enabled')->default(false);
            $table->string('formatter', 20)->default('unformatted');
            $table->string('wrapper_class')->nullable();
            $table->string('wrapper_id')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};

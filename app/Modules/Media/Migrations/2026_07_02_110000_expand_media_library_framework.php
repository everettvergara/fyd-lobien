<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_folders', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->unsignedInteger('sort_order')->default(0)->after('parent_id');
            $table->foreignId('created_by')->nullable()->after('sort_order')->constrained('users')->nullOnDelete();
            $table->softDeletes();

            $table->index(['parent_id', 'sort_order']);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->char('uuid', 36)->nullable()->unique()->after('id');
            $table->string('title')->nullable()->after('original_filename');
            $table->text('description')->nullable()->after('title');
            $table->text('caption')->nullable()->after('description');
            $table->string('copyright')->nullable()->after('alt_text');
            $table->string('credit')->nullable()->after('copyright');
            $table->string('extension', 20)->nullable()->after('mime_type');
            $table->unsignedInteger('width')->nullable()->after('size');
            $table->unsignedInteger('height')->nullable()->after('width');
            $table->unsignedInteger('duration')->nullable()->after('height');
            $table->string('storage_provider', 50)->default('local')->after('disk');
            $table->string('visibility', 20)->default('public')->after('path');
            $table->string('checksum', 64)->nullable()->after('visibility');
            $table->timestamp('archived_at')->nullable()->after('uploaded_by');
            $table->softDeletes();

            $table->index(['folder_id', 'created_at']);
            $table->index(['mime_type', 'created_at']);
            $table->index(['uploaded_by', 'created_at']);
            $table->index(['size']);
            $table->index(['archived_at']);
            $table->index(['deleted_at']);
            $table->index(['checksum']);
        });

        Schema::create('media_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('variant', 50);
            $table->string('disk', 50)->default('public');
            $table->string('storage_provider', 50)->default('local');
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['media_id', 'variant']);
        });

        Schema::create('media_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('media_media_tag', function (Blueprint $table) {
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->foreignId('media_tag_id')->constrained('media_tags')->cascadeOnDelete();

            $table->primary(['media_id', 'media_tag_id']);
        });

        Schema::create('media_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('usable_type');
            $table->unsignedBigInteger('usable_id');
            $table->string('module', 100);
            $table->string('field', 100);
            $table->string('label')->nullable();
            $table->timestamps();

            $table->unique(['media_id', 'usable_type', 'usable_id', 'field'], 'media_usage_unique');
            $table->index(['usable_type', 'usable_id']);
            $table->index(['module', 'field']);
        });

        Schema::create('media_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->json('properties')->nullable();
            $table->timestamps();

            $table->index(['media_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });

        Schema::create('media_user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('key', 100);
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_user_preferences');
        Schema::dropIfExists('media_history');
        Schema::dropIfExists('media_usage');
        Schema::dropIfExists('media_media_tag');
        Schema::dropIfExists('media_tags');
        Schema::dropIfExists('media_variants');

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['folder_id', 'created_at']);
            $table->dropIndex(['mime_type', 'created_at']);
            $table->dropIndex(['uploaded_by', 'created_at']);
            $table->dropIndex(['size']);
            $table->dropIndex(['archived_at']);
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['checksum']);
            $table->dropUnique(['uuid']);

            $table->dropColumn([
                'uuid',
                'title',
                'description',
                'caption',
                'copyright',
                'credit',
                'extension',
                'width',
                'height',
                'duration',
                'storage_provider',
                'visibility',
                'checksum',
                'archived_at',
                'deleted_at',
            ]);
        });

        Schema::table('media_folders', function (Blueprint $table) {
            $table->dropIndex(['parent_id', 'sort_order']);
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['slug', 'sort_order', 'deleted_at']);
        });
    }
};

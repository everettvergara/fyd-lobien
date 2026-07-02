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
        Schema::table('banners', function (Blueprint $table) {
            $table->string('key')->nullable()->after('name');
        });

        $usedKeys = [];

        DB::table('banners')->orderBy('id')->get()->each(function ($banner) use (&$usedKeys) {
            $base = $banner->slug
                ? Str::slug($banner->slug)
                : Str::slug(str_replace('_', '-', $banner->placement ?: $banner->name));

            if ($base === '') {
                $base = 'banner-'.$banner->id;
            }

            $key = $base;
            $suffix = 1;

            while (in_array($key, $usedKeys, true) || DB::table('banners')->where('key', $key)->where('id', '!=', $banner->id)->exists()) {
                $key = $base.'-'.$suffix;
                $suffix++;
            }

            $usedKeys[] = $key;

            DB::table('banners')->where('id', $banner->id)->update(['key' => $key]);
        });

        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'placement')) {
                $table->dropIndex(['placement', 'sort_order']);
            }

            if (Schema::hasColumn('banners', 'status')) {
                $table->dropIndex(['status', 'published_at', 'expires_at']);
            }

            if (Schema::hasColumn('banners', 'visibility')) {
                $table->dropIndex(['visibility', 'updated_at']);
            }

            $table->unique('key');
        });

        Schema::table('banners', function (Blueprint $table) {
            $columns = ['placement', 'slug', 'visibility', 'published_at', 'expires_at', 'timezone'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('banners', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('banner_placements');
    }

    public function down(): void
    {
        Schema::create('banner_placements', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->string('placement')->default('homepage_hero')->after('template_id');
            $table->string('slug')->nullable()->after('name');
            $table->string('visibility')->default('published')->after('status');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('timezone', 80)->default('UTC');
            $table->dropUnique(['key']);
            $table->dropColumn('key');
            $table->index(['placement', 'sort_order']);
            $table->index(['status', 'published_at', 'expires_at']);
            $table->index(['visibility', 'updated_at']);
        });
    }
};

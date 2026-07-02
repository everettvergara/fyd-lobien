<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->boolean('sitemap_include')->default(true)->after('robots');
            $table->string('sitemap_changefreq', 20)->nullable()->after('sitemap_include');
            $table->decimal('sitemap_priority', 2, 1)->nullable()->after('sitemap_changefreq');
        });
    }

    public function down(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->dropColumn(['sitemap_include', 'sitemap_changefreq', 'sitemap_priority']);
        });
    }
};

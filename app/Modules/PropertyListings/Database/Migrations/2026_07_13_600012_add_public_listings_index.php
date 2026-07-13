<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('listings')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            $table->index(['published_to_public', 'city', 'slug'], 'listings_public_city_slug_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('listings')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex('listings_public_city_slug_index');
        });
    }
};

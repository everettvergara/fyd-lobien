<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('listings') && Schema::hasColumn('listings', 'published_to_public')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->boolean('published_to_public')->nullable()->default(null)->change();
            });
        }

        if (Schema::hasTable('listing_other_infos') && Schema::hasColumn('listing_other_infos', 'other_info_visible')) {
            Schema::table('listing_other_infos', function (Blueprint $table) {
                $table->boolean('other_info_visible')->nullable()->default(null)->change();
            });
        }

        if (Schema::hasTable('listing_units')) {
            Schema::table('listing_units', function (Blueprint $table) {
                if (Schema::hasColumn('listing_units', 'for_lease')) {
                    $table->boolean('for_lease')->nullable()->default(null)->change();
                }

                if (Schema::hasColumn('listing_units', 'for_sale')) {
                    $table->boolean('for_sale')->nullable()->default(null)->change();
                }

                if (Schema::hasColumn('listing_units', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->nullable()->default(null)->change();
                }
            });
        }

        if (Schema::hasTable('listing_fees') && Schema::hasColumn('listing_fees', 'sort_order')) {
            Schema::table('listing_fees', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->nullable()->default(null)->change();
            });
        }
    }

    public function down(): void
    {
        // Keep nullable to avoid breaking imported warning rows that intentionally blank invalid values.
    }
};

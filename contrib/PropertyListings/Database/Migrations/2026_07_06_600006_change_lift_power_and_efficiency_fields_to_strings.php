<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('listing_specs') && Schema::hasColumn('listing_specs', 'floor_efficiency')) {
            Schema::table('listing_specs', function (Blueprint $table) {
                $table->string('floor_efficiency')->nullable()->change();
            });
        }

        if (Schema::hasTable('listing_building_services')) {
            Schema::table('listing_building_services', function (Blueprint $table) {
                if (Schema::hasColumn('listing_building_services', 'no_of_lifts_passenger')) {
                    $table->string('no_of_lifts_passenger')->nullable()->change();
                }

                if (Schema::hasColumn('listing_building_services', 'no_of_lifts_service')) {
                    $table->string('no_of_lifts_service')->nullable()->change();
                }

                if (Schema::hasColumn('listing_building_services', 'backup_power')) {
                    $table->string('backup_power')->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        // Keep string types to avoid losing text values imported after this migration.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('listing_specs') || ! Schema::hasColumn('listing_specs', 'density_ratio')) {
            return;
        }

        Schema::table('listing_specs', function (Blueprint $table) {
            $table->string('density_ratio', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Keep as string to avoid losing non-numeric ratio values such as "1:450".
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('listing_fees') || ! Schema::hasColumn('listing_fees', 'fee_type')) {
            return;
        }

        Schema::table('listing_fees', function (Blueprint $table) {
            $table->string('fee_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Keep nullable so imports that intentionally blank unknown fee types do not fail on rollback.
    }
};

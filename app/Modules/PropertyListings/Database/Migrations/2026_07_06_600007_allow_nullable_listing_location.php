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
            if (Schema::hasColumn('listings', 'province')) {
                $table->string('province')->nullable()->change();
            }

            if (Schema::hasColumn('listings', 'city')) {
                $table->string('city')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        // Keep nullable to preserve listings intentionally saved without location.
    }
};

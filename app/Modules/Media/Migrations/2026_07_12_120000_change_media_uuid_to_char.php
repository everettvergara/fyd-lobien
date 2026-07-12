<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('media') || ! Schema::hasColumn('media', 'uuid')) {
            return;
        }

        Schema::table('media', function (Blueprint $table) {
            $table->char('uuid', 36)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('media') || ! Schema::hasColumn('media', 'uuid')) {
            return;
        }

        // Keep CHAR(36) — live databases may not support native UUID.
    }
};

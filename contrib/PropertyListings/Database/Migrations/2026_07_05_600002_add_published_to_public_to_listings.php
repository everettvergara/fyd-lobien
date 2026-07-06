<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->boolean('published_to_public')->default(false)->after('completion_status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('listings') || ! Schema::hasColumn('listings', 'published_to_public')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('published_to_public');
        });
    }
};

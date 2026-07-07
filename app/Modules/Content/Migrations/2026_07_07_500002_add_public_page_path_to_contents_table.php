<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contents')) {
            return;
        }

        Schema::table('contents', function (Blueprint $table) {
            if (! Schema::hasColumn('contents', 'public_page_path')) {
                $table->string('public_page_path')->nullable()->after('author_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('contents') || ! Schema::hasColumn('contents', 'public_page_path')) {
            return;
        }

        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('public_page_path');
        });
    }
};

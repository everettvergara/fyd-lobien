<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->string('icon', 100)->default('bi-view-stacked')->after('name');
        });

        if (Schema::hasTable('content_blocks')) {
            DB::table('content_blocks')->where('key', 'latest-articles')->update(['icon' => 'bi-newspaper']);
            DB::table('content_blocks')->where('key', 'featured-pages')->update(['icon' => 'bi-grid']);
        }
    }

    public function down(): void
    {
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};

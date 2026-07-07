<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_types', function (Blueprint $table) {
            $table->string('slug', 100)->nullable()->unique()->after('key');
        });

        DB::table('content_types')->where('key', 'page')->update(['slug' => null]);
        DB::table('content_types')->where('key', 'article')->update(['slug' => 'articles']);
    }

    public function down(): void
    {
        Schema::table('content_types', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};

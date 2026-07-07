<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->foreignId('attachment_id')->nullable()->after('featured_image_id')->constrained('media')->nullOnDelete();
            $table->string('url_link', 2048)->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('attachment_id');
            $table->dropColumn('url_link');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_lookups', function (Blueprint $table) {
            $table->string('summary', 500)->nullable()->after('label');
            $table->longText('description')->nullable()->after('summary');
            $table->foreignId('image_id')->nullable()->after('description')->constrained('media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('listing_lookups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('image_id');
            $table->dropColumn(['summary', 'description']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('avatar_media_id')->nullable()->after('email')->constrained('media')->nullOnDelete();
            $table->string('contact_number')->nullable()->after('avatar_media_id');
            $table->foreignId('province_id')->nullable()->after('contact_number')->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->after('province_id')->constrained()->nullOnDelete();
            $table->text('about_me')->nullable()->after('city_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
            $table->dropConstrainedForeignId('province_id');
            $table->dropConstrainedForeignId('avatar_media_id');
            $table->dropColumn(['contact_number', 'about_me']);
        });
    }
};

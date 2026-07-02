<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->string('path', 500);
            $table->string('route_name', 100)->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->string('referer_host', 255)->nullable();
            $table->timestamp('visited_at');

            $table->index('path');
            $table->index('ip_address');
            $table->index('referer_host');
            $table->index('visited_at');
            $table->index(['visited_at', 'path']);
            $table->index(['visited_at', 'ip_address']);
            $table->index(['visited_at', 'referer_host']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};

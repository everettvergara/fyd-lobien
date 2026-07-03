<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installed_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('status');
            $table->string('version')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installed_modules');
    }
};

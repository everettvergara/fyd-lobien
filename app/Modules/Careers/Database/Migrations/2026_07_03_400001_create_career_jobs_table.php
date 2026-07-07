<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('picture_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->string('salary_range')->nullable();
            $table->string('employment_type')->default('full_time');
            $table->text('summary')->nullable();
            $table->longText('description');
            $table->longText('requirements')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->date('closing_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_jobs');
    }
};

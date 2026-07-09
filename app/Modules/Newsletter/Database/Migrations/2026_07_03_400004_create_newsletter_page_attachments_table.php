<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_page_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_list_id')->constrained('newsletter_lists')->cascadeOnDelete();
            $table->string('content_slug')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_page_attachments');
    }
};

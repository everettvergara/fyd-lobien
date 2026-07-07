<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('webform_page_attachments');
    }

    public function down(): void
    {
        if (Schema::hasTable('webform_page_attachments')) {
            return;
        }

        Schema::create('webform_page_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webform_id')->constrained('webforms')->cascadeOnDelete();
            $table->string('content_slug')->unique();
            $table->timestamps();

            $table->index('webform_id');
        });
    }
};

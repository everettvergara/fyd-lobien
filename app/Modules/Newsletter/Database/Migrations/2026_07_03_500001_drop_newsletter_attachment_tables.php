<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('newsletter_section_attachments');
        Schema::dropIfExists('newsletter_page_attachments');
    }

    public function down(): void
    {
        // Attachment tables were replaced by Page Manager blocks.
    }
};

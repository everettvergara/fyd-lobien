<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webform_page_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webform_id')->constrained('webforms')->cascadeOnDelete();
            $table->string('content_slug')->unique();
            $table->timestamps();

            $table->index('webform_id');
        });

        if (Schema::hasColumn('webforms', 'content_slug')) {
            DB::table('webforms')
                ->whereNotNull('content_slug')
                ->where('content_slug', '!=', '')
                ->orderBy('id')
                ->get()
                ->each(function ($webform) {
                    DB::table('webform_page_attachments')->insert([
                        'webform_id' => $webform->id,
                        'content_slug' => $webform->content_slug,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });

            Schema::table('webforms', function (Blueprint $table) {
                $table->dropColumn('content_slug');
            });
        }
    }

    public function down(): void
    {
        Schema::table('webforms', function (Blueprint $table) {
            if (! Schema::hasColumn('webforms', 'content_slug')) {
                $table->string('content_slug')->nullable()->index()->after('description');
            }
        });

        if (Schema::hasTable('webform_page_attachments')) {
            foreach (DB::table('webform_page_attachments')->orderBy('id')->get() as $attachment) {
                DB::table('webforms')
                    ->where('id', $attachment->webform_id)
                    ->whereNull('content_slug')
                    ->update(['content_slug' => $attachment->content_slug]);
            }

            Schema::dropIfExists('webform_page_attachments');
        }
    }
};

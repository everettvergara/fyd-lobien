<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contents')) {
            return;
        }

        $foreignKeys = collect(Schema::getForeignKeys('contents'))
            ->pluck('name')
            ->all();

        Schema::table('contents', function (Blueprint $table) use ($foreignKeys) {
            if (in_array('pages_author_id_foreign', $foreignKeys, true)) {
                $table->dropForeign('pages_author_id_foreign');
                $table->foreign('author_id', 'contents_author_id_foreign')
                    ->references('id')
                    ->on('users');
            }

            if (in_array('pages_featured_image_id_foreign', $foreignKeys, true)) {
                $table->dropForeign('pages_featured_image_id_foreign');
                $table->foreign('featured_image_id', 'contents_featured_image_id_foreign')
                    ->references('id')
                    ->on('media')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Do not restore pages_* names — that recreates the global collision with PageManager's pages table.
    }
};

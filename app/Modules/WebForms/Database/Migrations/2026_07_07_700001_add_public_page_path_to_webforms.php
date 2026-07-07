<?php

use App\Modules\WebForms\Services\WebformPageSyncService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webforms', function (Blueprint $table) {
            $table->string('public_page_path')->nullable()->after('sort_order');
        });

        if (Schema::hasTable('webforms') && Schema::hasTable('pages')) {
            app(WebformPageSyncService::class)->syncAll();
        }
    }

    public function down(): void
    {
        Schema::table('webforms', function (Blueprint $table) {
            $table->dropColumn('public_page_path');
        });
    }
};

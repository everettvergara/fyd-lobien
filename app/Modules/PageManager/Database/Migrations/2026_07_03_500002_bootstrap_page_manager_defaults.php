<?php

use App\Modules\PageManager\Models\PageMaster;
use App\Modules\PageManager\Services\PageManagerService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        PageMaster::instance();
        app(PageManagerService::class)->ensureRootPage();
    }

    public function down(): void
    {
        // System root page is required for the public site.
    }
};

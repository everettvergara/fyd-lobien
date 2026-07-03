<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_blocks')) {
            DB::table('page_blocks')
                ->whereIn('block_type', ['webform', 'careers-listing'])
                ->delete();
        }

        if (Schema::hasTable('page_master_blocks')) {
            DB::table('page_master_blocks')
                ->whereIn('block_type', ['webform', 'careers-listing'])
                ->delete();
        }
    }

    public function down(): void
    {
        // Block placement is admin-managed; do not re-seed module blocks.
    }
};

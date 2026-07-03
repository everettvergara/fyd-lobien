<?php

namespace App\Services\Module;

use App\Framework\Module;
use App\Framework\PublicBlock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModulePageBlockCleanupService
{
    public function removeBlocksForModule(Module $module): void
    {
        $blockTypes = collect($module->publicBlocks())
            ->map(fn (PublicBlock $block) => $block->key())
            ->filter()
            ->values()
            ->all();

        if ($blockTypes === []) {
            return;
        }

        if (Schema::hasTable('page_blocks')) {
            DB::table('page_blocks')->whereIn('block_type', $blockTypes)->delete();
        }

        if (Schema::hasTable('page_master_blocks')) {
            DB::table('page_master_blocks')->whereIn('block_type', $blockTypes)->delete();
        }
    }
}

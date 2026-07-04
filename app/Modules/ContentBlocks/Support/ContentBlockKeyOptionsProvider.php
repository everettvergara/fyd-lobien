<?php

namespace App\Modules\ContentBlocks\Support;

use App\Contracts\BlockConfigOptionsProvider;
use App\Modules\ContentBlocks\Models\ContentBlock;

class ContentBlockKeyOptionsProvider implements BlockConfigOptionsProvider
{
    public function options(): array
    {
        return ContentBlock::query()
            ->published()
            ->orderBy('name')
            ->get(['key', 'name'])
            ->map(fn (ContentBlock $block) => [
                'value' => $block->key,
                'label' => $block->name,
                'icon' => $block->icon,
            ])
            ->all();
    }
}

<?php

namespace App\Modules\Content\Support;

use App\Contracts\BlockConfigOptionsProvider;
use App\Modules\Content\Models\ContentType;

class ContentTypeKeyOptionsProvider implements BlockConfigOptionsProvider
{
    public function options(): array
    {
        return ContentType::query()
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get(['key', 'label'])
            ->map(fn (ContentType $type) => [
                'value' => $type->key,
                'label' => $type->label,
            ])
            ->all();
    }
}

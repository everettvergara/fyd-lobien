<?php

namespace App\Modules\Newsletter\Support;

use App\Contracts\BlockConfigOptionsProvider;
use App\Modules\Newsletter\Models\NewsletterList;

class NewsletterListOptionsProvider implements BlockConfigOptionsProvider
{
    public function options(): array
    {
        return NewsletterList::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['slug', 'name'])
            ->map(fn (NewsletterList $list) => [
                'value' => (string) $list->slug,
                'label' => (string) $list->name,
            ])
            ->values()
            ->all();
    }
}

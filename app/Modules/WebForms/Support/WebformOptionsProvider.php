<?php

namespace App\Modules\WebForms\Support;

use App\Contracts\BlockConfigOptionsProvider;
use App\Modules\WebForms\Models\Webform;

class WebformOptionsProvider implements BlockConfigOptionsProvider
{
    public function options(): array
    {
        return Webform::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['slug', 'name'])
            ->map(fn (Webform $webform) => [
                'value' => (string) $webform->slug,
                'label' => (string) $webform->name,
            ])
            ->values()
            ->all();
    }
}

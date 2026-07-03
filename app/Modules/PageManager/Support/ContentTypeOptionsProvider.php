<?php

namespace App\Modules\PageManager\Support;

use App\Contracts\BlockConfigOptionsProvider;
use App\Support\ContentTypeRegistry;

class ContentTypeOptionsProvider implements BlockConfigOptionsProvider
{
    public function __construct(
        protected ContentTypeRegistry $contentTypes,
    ) {}

    public function options(): array
    {
        return collect($this->contentTypes->options())
            ->map(fn (string $label, string $value) => [
                'value' => $value,
                'label' => $label,
            ])
            ->values()
            ->all();
    }
}

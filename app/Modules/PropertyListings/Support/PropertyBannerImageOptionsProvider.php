<?php

namespace App\Modules\PropertyListings\Support;

use App\Contracts\BlockConfigOptionsProvider;
use App\Models\Media;

class PropertyBannerImageOptionsProvider implements BlockConfigOptionsProvider
{
    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function options(): array
    {
        $options = Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->map(fn (Media $media) => [
                'value' => (string) $media->id,
                'label' => $media->displayName(),
            ])
            ->values()
            ->all();

        return [
            ['value' => '', 'label' => '— No background image —'],
            ...$options,
        ];
    }
}

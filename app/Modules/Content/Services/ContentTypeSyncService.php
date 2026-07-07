<?php

namespace App\Modules\Content\Services;

use App\Modules\Content\Models\ContentType;
use App\Support\ContentTypeRegistry;
use Illuminate\Support\Facades\Schema;

class ContentTypeSyncService
{
    public function syncFromConfig(): void
    {
        if (! Schema::hasTable('content_types')) {
            return;
        }

        $sort = 0;
        foreach (config('content-types', []) as $key => $type) {
            ContentType::updateOrCreate(
                ['key' => $key],
                [
                    'label' => $type['label'] ?? $key,
                    'slug' => $type['slug'] ?? null,
                    'description' => $type['description'] ?? null,
                    'icon' => $type['icon'] ?? 'bi-file-earmark',
                    'sort_order' => $sort++,
                    'is_active' => true,
                ],
            );
        }

        app(ContentTypeRegistry::class)->forgetCache();
    }
}

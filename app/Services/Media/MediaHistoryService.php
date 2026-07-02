<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Services\ActivityLogger;

class MediaHistoryService
{
    public function record(Media $media, string $action, array $properties = [], ?int $userId = null): void
    {
        $media->history()->create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'properties' => $properties ?: null,
        ]);

        ActivityLogger::log('media', $action, $media, $properties);
    }
}

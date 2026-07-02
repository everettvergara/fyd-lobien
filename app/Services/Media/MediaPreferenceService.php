<?php

namespace App\Services\Media;

use App\Models\MediaUserPreference;

class MediaPreferenceService
{
    public function get(int $userId, string $key, mixed $default = null): mixed
    {
        $preference = MediaUserPreference::where('user_id', $userId)
            ->where('key', $key)
            ->first();

        return $preference?->value ?? $default;
    }

    public function set(int $userId, string $key, mixed $value): void
    {
        MediaUserPreference::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value],
        );
    }
}

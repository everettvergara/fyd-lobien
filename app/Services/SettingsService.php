<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    protected int $ttl = 3600;

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        return Cache::remember(
            $this->cacheKey($group, $key),
            $this->ttl,
            fn () => $this->resolveFromDatabase($group, $key, $default)
        );
    }

    public function set(string $group, string $key, mixed $value, string $type = 'string'): void
    {
        $stored = is_array($value) ? json_encode($value) : (string) $value;

        Setting::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $stored, 'type' => $type]
        );

        $this->forget($group, $key);
    }

    /**
     * @return array<string, mixed>
     */
    public function group(string $group): array
    {
        return Cache::remember(
            $this->groupCacheKey($group),
            $this->ttl,
            fn () => Setting::where('group', $group)->pluck('value', 'key')->toArray()
        );
    }

    public function forget(string $group, ?string $key = null): void
    {
        if ($key !== null) {
            Cache::forget($this->cacheKey($group, $key));
        }

        Cache::forget($this->groupCacheKey($group));
    }

    public function flush(): void
    {
        Cache::flush();
    }

    protected function cacheKey(string $group, string $key): string
    {
        return "settings.{$group}.{$key}";
    }

    protected function groupCacheKey(string $group): string
    {
        return "settings.group.{$group}";
    }

    protected function resolveFromDatabase(string $group, string $key, mixed $default): mixed
    {
        $setting = Setting::where('group', $group)->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }
}

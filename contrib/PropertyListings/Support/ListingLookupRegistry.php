<?php

namespace App\Modules\PropertyListings\Support;

use App\Modules\PropertyListings\Models\ListingLookup;
use Illuminate\Support\Facades\Cache;

class ListingLookupRegistry
{
    protected const CACHE_KEY = 'property-listings.lookups';

    /**
     * @return array<string, array<int, array{value: string, label: string, meta: array<string, mixed>}>>
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return ListingLookup::query()
                ->where('is_active', true)
                ->orderBy('group')
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get()
                ->groupBy('group')
                ->map(fn ($items) => $items->map(fn (ListingLookup $item) => [
                    'value' => $item->value,
                    'label' => $item->label,
                    'meta' => $item->meta ?? [],
                ])->values()->all())
                ->all();
        });
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, string>
     */
    public function options(string $group): array
    {
        $items = $this->all()[$group] ?? [];

        return collect($items)->mapWithKeys(fn (array $item) => [
            $item['value'] => $item['label'],
        ])->all();
    }

    public function label(string $group, ?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return $this->options($group)[$value] ?? $value;
    }

    /**
     * @return array<int, string>
     */
    public function values(string $group): array
    {
        return array_keys($this->options($group));
    }

    public function hasValue(string $group, string $value): bool
    {
        return array_key_exists($value, $this->options($group));
    }

    public function fileKind(string $group, string $value): ?string
    {
        if (! ListingLookupGroups::usesFileKind($group)) {
            return null;
        }

        $items = $this->all()[$group] ?? [];

        foreach ($items as $item) {
            if ($item['value'] === $value) {
                return (string) ($item['meta']['file_kind'] ?? 'image');
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public function allowedExtensions(string $group, string $value): array
    {
        $kind = $this->fileKind($group, $value);

        return match ($kind) {
            'pdf' => ['pdf'],
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'],
            default => [],
        };
    }
}

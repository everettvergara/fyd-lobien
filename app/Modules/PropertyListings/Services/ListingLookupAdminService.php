<?php

namespace App\Modules\PropertyListings\Services;

use App\Modules\PropertyListings\Models\ListingLookup;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use Illuminate\Support\Str;

class ListingLookupAdminService
{
    /**
     * @return array<int, array{group: string, label: string, slug: string, active_count: int}>
     */
    public function hubStats(): array
    {
        $counts = ListingLookup::query()
            ->where('is_active', true)
            ->selectRaw('`group`, COUNT(*) as aggregate')
            ->groupBy('group')
            ->pluck('aggregate', 'group');

        return collect(ListingLookupGroups::labels())
            ->map(fn (string $label, string $group) => [
                'group' => $group,
                'label' => $label,
                'slug' => $this->groupSlug($group),
                'active_count' => (int) ($counts[$group] ?? 0),
            ])
            ->values()
            ->all();
    }

    public function groupSlug(string $group): string
    {
        return Str::slug(str_replace('_', '-', $group));
    }

    public function groupFromSlug(string $slug): ?string
    {
        $normalized = Str::lower(trim($slug));

        foreach (ListingLookupGroups::keys() as $group) {
            if ($this->groupSlug($group) === $normalized) {
                return $group;
            }
        }

        return null;
    }

    public function groupLabel(string $group): string
    {
        return ListingLookupGroups::label($group);
    }

    public function isKnownGroup(string $group): bool
    {
        return ListingLookupGroups::has($group);
    }
}

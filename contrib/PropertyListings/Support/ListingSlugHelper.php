<?php

namespace App\Modules\PropertyListings\Support;

use App\Modules\PropertyListings\Models\Listing;
use Illuminate\Support\Str;

class ListingSlugHelper
{
    public function generateFromName(string $name, ?string $fallbackCode = null): string
    {
        $base = Str::slug($name);

        if ($base === '' && $fallbackCode !== null) {
            $base = Str::slug($fallbackCode);
        }

        if ($base === '') {
            $base = 'listing';
        }

        return $base;
    }

    public function ensureUnique(string $base, ?string $city, ?int $ignoreListingId = null): string
    {
        $slug = $base;
        $suffix = 2;

        while ($this->slugExists($slug, $city, $ignoreListingId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, ?string $city, ?int $ignoreListingId = null): bool
    {
        $query = Listing::query()->where('slug', $slug);

        if ($city === null || trim($city) === '') {
            $query->where(function ($builder) {
                $builder->whereNull('city')->orWhere('city', '');
            });
        } else {
            $query->where('city', $city);
        }

        if ($ignoreListingId !== null) {
            $query->where('id', '!=', $ignoreListingId);
        }

        return $query->exists();
    }
}

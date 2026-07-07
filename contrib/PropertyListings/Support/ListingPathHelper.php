<?php

namespace App\Modules\PropertyListings\Support;

use App\Modules\PropertyListings\Models\Listing;
use Illuminate\Support\Str;

class ListingPathHelper
{
    public const PREFIX = '/properties';

    /**
     * Path segments under the prefix that can never be city slugs.
     *
     * @var array<int, string>
     */
    public const RESERVED_SEGMENTS = ['search'];

    public static function citySlug(?string $city): ?string
    {
        if ($city === null || trim($city) === '') {
            return null;
        }

        $slug = Str::slug($city);

        if ($slug === '' || in_array($slug, self::RESERVED_SEGMENTS, true)) {
            return null;
        }

        return $slug;
    }

    public static function indexPath(): string
    {
        return self::PREFIX;
    }

    public static function searchPath(): string
    {
        return self::PREFIX.'/search';
    }

    public static function listingPath(Listing $listing): ?string
    {
        $citySlug = self::citySlug($listing->city);
        $listingSlug = trim((string) ($listing->slug ?? ''));

        if ($citySlug === null || $listingSlug === '') {
            return null;
        }

        return self::PREFIX.'/'.$citySlug.'/'.$listingSlug;
    }

    public static function cityPath(string $citySlug): string
    {
        return self::PREFIX.'/'.trim($citySlug, '/');
    }

    /**
     * @return array{city_slug: string, listing_slug?: string}|null
     */
    public static function parsePagePath(string $pagePath): ?array
    {
        $normalized = '/'.trim($pagePath, '/');
        $prefix = self::PREFIX;

        if ($normalized === $prefix || ! str_starts_with($normalized, $prefix.'/')) {
            return null;
        }

        $remainder = substr($normalized, strlen($prefix) + 1);
        if ($remainder === false || $remainder === '') {
            return null;
        }

        $segments = array_values(array_filter(explode('/', $remainder), fn (string $part) => $part !== ''));

        if ($segments === [] || in_array($segments[0], self::RESERVED_SEGMENTS, true)) {
            return null;
        }

        if (count($segments) === 1) {
            return ['city_slug' => $segments[0]];
        }

        if (count($segments) === 2) {
            return [
                'city_slug' => $segments[0],
                'listing_slug' => $segments[1],
            ];
        }

        return null;
    }

    public static function isPropertyListingPath(string $pagePath): bool
    {
        return self::parsePagePath($pagePath) !== null;
    }
}

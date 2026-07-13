<?php

namespace App\Modules\PropertyListings\Support;

class ListingCompareFormatter
{
    public static function money(mixed $value): string
    {
        $formatted = self::formatNumber($value);

        return $formatted === null ? '—' : '₱'.$formatted;
    }

    public static function area(mixed $value): string
    {
        $formatted = self::formatNumber($value);

        return $formatted ?? '—';
    }

    protected static function formatNumber(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', ',');
    }
}

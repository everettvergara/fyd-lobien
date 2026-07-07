<?php

namespace App\Support;

class SlugValidation
{
    public const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /**
     * @return array<int, mixed>
     */
    public static function rules(string $uniqueRule): array
    {
        return [
            'required',
            'string',
            'max:255',
            'regex:'.self::PATTERN,
            $uniqueRule,
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function nullableRules(string $uniqueRule): array
    {
        return [
            'nullable',
            'string',
            'max:100',
            'regex:'.self::PATTERN,
            $uniqueRule,
        ];
    }
}

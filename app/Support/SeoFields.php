<?php

namespace App\Support;

class SeoFields
{
    public static function rules(): array
    {
        return [
            'seo_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image_id' => ['nullable', 'exists:media,id'],
            'robots' => ['nullable', 'string', 'max:100'],
        ];
    }

    public static function extract(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'seo_title', 'meta_description', 'meta_keywords', 'canonical_url',
            'og_title', 'og_description', 'og_image_id', 'robots',
        ]));
    }
}

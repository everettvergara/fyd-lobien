<?php

namespace App\Support;

use App\Enums\SitemapChangeFrequency;
use Illuminate\Validation\Rule;

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
            'sitemap_include' => ['nullable', 'boolean'],
            'sitemap_changefreq' => ['nullable', Rule::enum(SitemapChangeFrequency::class)],
            'sitemap_priority' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ];
    }

    public static function extract(array $data): array
    {
        return array_intersect_key($data, array_flip(self::attributeKeys()));
    }

    /**
     * @return array<int, string>
     */
    public static function attributeKeys(): array
    {
        return array_keys(self::rules());
    }
}

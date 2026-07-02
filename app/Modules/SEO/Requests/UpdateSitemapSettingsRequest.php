<?php

namespace App\Modules\SEO\Requests;

use App\Enums\SitemapChangeFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSitemapSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('seo.edit') ?? false;
    }

    public function rules(): array
    {
        $frequencies = array_column(SitemapChangeFrequency::cases(), 'value');

        return [
            'sitemap_enabled' => ['required', 'boolean'],
            'homepage_include' => ['required', 'boolean'],
            'homepage_changefreq' => ['required', Rule::in($frequencies)],
            'homepage_priority' => ['required', 'numeric', 'min:0', 'max:1'],
            'default_changefreq_page' => ['required', Rule::in($frequencies)],
            'default_changefreq_article' => ['required', Rule::in($frequencies)],
            'default_priority' => ['required', 'numeric', 'min:0', 'max:1'],
        ];
    }
}

<?php

namespace App\Modules\PageManager\Requests;

use App\Support\SeoFields;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePageMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', \App\Modules\PageManager\Models\PageMaster::instance());
    }

    public function rules(): array
    {
        return array_merge([
            'default_seo_title_suffix' => ['nullable', 'string', 'max:255'],
            'default_robots' => ['nullable', 'string', 'max:100'],
            'default_sitemap_changefreq' => ['nullable', 'string', 'max:50'],
            'default_sitemap_priority' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'blocks' => ['nullable', 'array'],
            'blocks.*.region_key' => ['required', 'string', 'max:80'],
            'blocks.*.block_type' => ['required', 'string', 'max:80'],
            'blocks.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'blocks.*.config' => ['nullable', 'array'],
        ], SeoFields::rules());
    }
}

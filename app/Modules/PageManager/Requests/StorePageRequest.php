<?php

namespace App\Modules\PageManager\Requests;

use App\Enums\ContentStatus;
use App\Support\HtmlSanitizer;
use App\Support\SeoFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Modules\PageManager\Models\Page::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('body')) {
            $this->merge(['body' => HtmlSanitizer::clean($this->input('body'))]);
        }

        if ($this->has('path')) {
            $this->merge(['path' => \App\Modules\PageManager\Models\Page::normalizePath((string) $this->input('path'))]);
        }
    }

    public function rules(): array
    {
        return array_merge([
            'path' => ['required', 'string', 'max:255', 'unique:pages,path'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'featured_image_id' => ['nullable', 'exists:media,id'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'published_at' => ['nullable', 'date'],
            'blocks' => ['nullable', 'array'],
            'blocks.*.region_key' => ['required', 'string', 'max:80'],
            'blocks.*.block_type' => ['required', 'string', 'max:80'],
            'blocks.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'blocks.*.config' => ['nullable', 'array'],
        ], SeoFields::rules());
    }
}

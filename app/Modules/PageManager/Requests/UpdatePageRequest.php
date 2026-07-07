<?php

namespace App\Modules\PageManager\Requests;

use App\Enums\ContentStatus;
use App\Support\HtmlSanitizer;
use App\Support\SeoFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('page'));
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('body')) {
            $this->merge(['body' => HtmlSanitizer::clean($this->input('body'))]);
        }

        $page = $this->route('page');

        if ($page?->is_system) {
            $this->merge(['path' => '/']);
        } elseif ($this->has('path')) {
            $normalizedPath = \App\Modules\PageManager\Models\Page::normalizePath((string) $this->input('path'));
            \App\Modules\PageManager\Models\Page::purgeSoftDeletedAtPath($normalizedPath);
            $this->merge(['path' => $normalizedPath]);
        }
    }

    public function rules(): array
    {
        $page = $this->route('page');

        return array_merge([
            'path' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'path')->ignore($page?->id),
            ],
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

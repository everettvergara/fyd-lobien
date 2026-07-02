<?php

namespace App\Modules\Pages\Requests;

use App\Enums\ContentStatus;
use App\Support\SeoFields;
use App\Support\SlugValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('page'));
    }

    public function rules(): array
    {
        return array_merge([
            'title' => ['required', 'string', 'max:255'],
            'slug' => SlugValidation::rules(Rule::unique('pages', 'slug')->ignore($this->route('page')->id)),
            'summary' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image_id' => ['nullable', 'exists:media,id'],
            'template' => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:pages,id'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'published_at' => ['nullable', 'date'],
            'sections' => ['nullable', 'array'],
            'sections.*.component_type' => ['required_with:sections', 'string'],
            'sections.*.settings' => ['nullable', 'array'],
        ], SeoFields::rules());
    }
}

<?php

namespace App\Modules\Content\Requests;

use App\Support\SlugValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreContentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Modules\Content\Models\ContentType::class);
    }

    public function rules(): array
    {
        return [
            'key' => array_merge(
                SlugValidation::rules('unique:content_types,key'),
                ['max:100'],
            ),
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}

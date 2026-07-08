<?php

namespace App\Modules\WebForms\Requests;

use App\Modules\WebForms\Models\Webform;
use App\Modules\WebForms\Requests\Concerns\ValidatesWebformPagePath;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreWebformRequest extends FormRequest
{
    use ValidatesWebformPagePath;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Webform::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('webforms', 'slug')],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            '_public_page_path' => $this->publicPagePathRules(),
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);

        $this->mergePublicPagePathForValidation();
    }
}

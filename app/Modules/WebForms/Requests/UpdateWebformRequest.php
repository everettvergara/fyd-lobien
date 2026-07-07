<?php

namespace App\Modules\WebForms\Requests;

use App\Modules\WebForms\Models\Webform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateWebformRequest extends FormRequest
{
    public function authorize(): bool
    {
        $webform = $this->route('webform');

        return $webform instanceof Webform
            && ($this->user()?->can('update', $webform) ?? false);
    }

    public function rules(): array
    {
        /** @var Webform $webform */
        $webform = $this->route('webform');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('webforms', 'slug')->ignore($webform->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('name') && ! $this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}

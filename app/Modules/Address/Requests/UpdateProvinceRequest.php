<?php

namespace App\Modules\Address\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProvinceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('province'));
    }

    public function rules(): array
    {
        $province = $this->route('province');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('provinces', 'name')->ignore($province->id)],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('provinces', 'code')->ignore($province->id)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}

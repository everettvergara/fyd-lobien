<?php

namespace App\Modules\Address\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('city'));
    }

    public function rules(): array
    {
        $city = $this->route('city');

        return [
            'province_id' => ['required', 'exists:provinces,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities', 'name')
                    ->where('province_id', $this->input('province_id'))
                    ->ignore($city->id),
            ],
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

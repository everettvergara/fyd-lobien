<?php

namespace App\Modules\Authentication\Requests;

use App\Modules\Authentication\Support\ProfileFieldRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
        ], ProfileFieldRules::rules());
    }

    protected function prepareForValidation(): void
    {
        ProfileFieldRules::prepare();
    }

    public function profileAttributes(): array
    {
        return $this->safe()->only(array_merge(
            ['name', 'email'],
            ProfileFieldRules::attributes(),
        ));
    }
}

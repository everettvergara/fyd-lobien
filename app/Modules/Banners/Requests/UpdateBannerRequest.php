<?php

namespace App\Modules\Banners\Requests;

use Illuminate\Validation\Rule;

class UpdateBannerRequest extends StoreBannerRequest
{
    public function authorize(): bool { return $this->user()->can('update', $this->route('banner')); }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('banners', 'key')->ignore($this->route('banner')),
            ],
        ];
    }
}

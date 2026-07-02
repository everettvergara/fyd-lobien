<?php

namespace App\Modules\Cache\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCacheSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('cache.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'ttl_days' => ['required', 'integer', 'min:1', 'max:365'],
        ];
    }
}

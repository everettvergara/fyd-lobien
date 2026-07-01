<?php

namespace App\Modules\Settings\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', Setting::class);
    }

    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.*' => ['array'],
            'settings.*.*' => ['nullable'],
            'settings.auth.password_min_length' => ['nullable', 'integer', 'min:6', 'max:128'],
            'settings.auth.login_max_attempts' => ['nullable', 'integer', 'min:1', 'max:20'],
            'settings.auth.session_lifetime' => ['nullable', 'integer', 'min:5', 'max:1440'],
        ];
    }
}

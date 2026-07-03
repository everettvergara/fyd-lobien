<?php

namespace App\Modules\Themes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstallThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('install', \App\Modules\Themes\Models\ThemeSettings::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'confirm' => ['accepted'],
        ];
    }
}

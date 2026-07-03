<?php

namespace App\Modules\Themes\Requests;

use App\Services\Theme\ThemeRegistryService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateThemeSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', \App\Modules\Themes\Models\ThemeSettings::class) ?? false;
    }

    public function rules(): array
    {
        $registry = app(ThemeRegistryService::class);
        $slugs = $registry->installed()
            ->filter(fn (array $theme) => $theme['valid'] ?? false)
            ->pluck('slug')
            ->all();

        return [
            'active_theme' => ['required', 'string', Rule::in($slugs)],
        ];
    }
}

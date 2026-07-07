<?php

namespace App\Modules\Settings\Requests;

use App\Models\Setting;
use App\Rules\PublishedContentPageUrl;
use App\Services\MailConfigService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'settings.email.mail_driver' => ['nullable', Rule::in(MailConfigService::ALLOWED_DRIVERS)],
            'settings.email.smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'settings.email.smtp_encryption' => ['nullable', Rule::in(['', 'tls', 'ssl'])],
            'settings.email.from_address' => ['nullable', 'email'],
            'settings.general.site_logo_id' => ['nullable', 'exists:media,id'],
            'settings.general.favicon_id' => ['nullable', 'exists:media,id'],
            'settings.general.maintenance_mode' => ['nullable', 'boolean'],
            'settings.general.maintenance_page_url' => [
                Rule::requiredIf(fn () => filter_var($this->input('settings.general.maintenance_mode', false), FILTER_VALIDATE_BOOLEAN)),
                'nullable',
                'string',
                'max:255',
                new PublishedContentPageUrl,
            ],
            'settings.social.facebook' => ['nullable', 'url', 'max:255'],
            'settings.social.instagram' => ['nullable', 'url', 'max:255'],
            'settings.social.linkedin' => ['nullable', 'url', 'max:255'],
            'settings.social.tiktok' => ['nullable', 'url', 'max:255'],
            'settings.social.youtube' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'settings.general.maintenance_page_url.required' => 'A maintenance page URL is required when maintenance mode is enabled.',
        ];
    }
}

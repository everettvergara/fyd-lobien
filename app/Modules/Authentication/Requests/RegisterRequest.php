<?php

namespace App\Modules\Authentication\Requests;

use App\Http\Requests\Public\Concerns\RequiresRecaptcha;
use App\Services\AuthConfigService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    use RequiresRecaptcha;

    public function authorize(): bool
    {
        return app(AuthConfigService::class)->registrationEnabled();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            ...$this->recaptchaRules('admin_register'),
        ];
    }
}

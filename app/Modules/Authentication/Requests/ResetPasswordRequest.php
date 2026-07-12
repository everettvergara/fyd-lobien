<?php

namespace App\Modules\Authentication\Requests;

use App\Http\Requests\Public\Concerns\RequiresRecaptcha;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    use RequiresRecaptcha;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            ...$this->recaptchaRules('admin_password_reset'),
        ];
    }
}

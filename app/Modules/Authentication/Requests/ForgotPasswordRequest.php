<?php

namespace App\Modules\Authentication\Requests;

use App\Http\Requests\Public\Concerns\RequiresRecaptcha;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    use RequiresRecaptcha;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            ...$this->recaptchaRules('admin_password_forgot'),
        ];
    }
}

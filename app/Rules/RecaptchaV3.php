<?php

namespace App\Rules;

use App\Services\Recaptcha\RecaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RecaptchaV3 implements ValidationRule
{
    public function __construct(
        protected string $action,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! app(RecaptchaService::class)->enabled()) {
            return;
        }

        if (! app(RecaptchaService::class)->verify($value, $this->action, request()->ip())) {
            $fail('Unable to verify that you are not a robot. Please try again.');
        }
    }
}

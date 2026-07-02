<?php

namespace App\Http\Requests\Public\Concerns;

use App\Rules\RecaptchaV3;
use App\Services\Recaptcha\RecaptchaService;

trait RequiresRecaptcha
{
    protected function recaptchaRules(string $action): array
    {
        if (! app(RecaptchaService::class)->enabled()) {
            return [];
        }

        return [
            'recaptcha_token' => ['required', new RecaptchaV3($action)],
        ];
    }
}

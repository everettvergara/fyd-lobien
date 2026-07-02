<?php

namespace App\Services\Recaptcha;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    protected const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public function enabled(): bool
    {
        return (bool) config('recaptcha.enabled');
    }

    public function verify(?string $token, string $action, ?string $ip = null): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        if (! filled($token)) {
            return false;
        }

        $response = Http::asForm()->post(self::VERIFY_URL, [
            'secret' => config('recaptcha.secret_key'),
            'response' => $token,
            'remoteip' => $ip,
        ]);

        if (! $response->successful()) {
            return false;
        }

        $payload = $response->json();

        if (! ($payload['success'] ?? false)) {
            return false;
        }

        if (($payload['action'] ?? null) !== $action) {
            return false;
        }

        return ($payload['score'] ?? 0) >= config('recaptcha.score_threshold');
    }
}

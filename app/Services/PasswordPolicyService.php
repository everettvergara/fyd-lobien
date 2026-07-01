<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class PasswordPolicyService
{
    public function __construct(
        protected SettingsService $settings,
    ) {}

    public function apply(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $minLength = (int) $this->settings->get('auth', 'password_min_length', 8);
        $mixedCase = $this->toBool($this->settings->get('auth', 'password_mixed_case', true));
        $numbers = $this->toBool($this->settings->get('auth', 'password_numbers', true));
        $symbols = $this->toBool($this->settings->get('auth', 'password_symbols', false));

        Password::defaults(function () use ($minLength, $mixedCase, $numbers, $symbols) {
            $rule = Password::min(max(6, $minLength));

            if ($mixedCase) {
                $rule->mixedCase();
            }

            if ($numbers) {
                $rule->numbers();
            }

            if ($symbols) {
                $rule->symbols();
            }

            return $rule;
        });
    }

    protected function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

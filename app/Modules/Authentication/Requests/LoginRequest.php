<?php

namespace App\Modules\Authentication\Requests;

use App\Enums\UserStatus;
use App\Services\AuthConfigService;
use App\Services\LoginHistoryService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            Auth::logout();
            $this->recordAuthFailure('email_not_verified');

            throw ValidationException::withMessages([
                'email' => 'Please verify your email address before signing in.',
            ]);
        }

        if (! $user->status->canLogin()) {
            Auth::logout();

            $reason = match ($user->status) {
                UserStatus::PendingVerification => 'pending_verification',
                UserStatus::Inactive => 'inactive',
                UserStatus::Suspended => 'suspended',
                UserStatus::Locked => 'locked',
                default => 'not_authorized',
            };

            $this->recordAuthFailure($reason);

            $message = match ($user->status) {
                UserStatus::PendingVerification => 'Your account is pending email verification.',
                UserStatus::Inactive => 'Your account is inactive. Please contact an administrator.',
                UserStatus::Suspended => 'Your account has been suspended.',
                UserStatus::Locked => 'Your account has been locked.',
                default => 'You are not authorized to sign in.',
            };

            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        $maxAttempts = app(AuthConfigService::class)->loginMaxAttempts();

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    protected function recordAuthFailure(string $reason): void
    {
        app(LoginHistoryService::class)->recordFailure(
            $this->string('email'),
            $this,
            $reason
        );
    }
}

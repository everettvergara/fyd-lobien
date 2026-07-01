<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\LoginHistoryService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogAuthenticationEvents
{
    public function __construct(
        protected LoginHistoryService $loginHistory,
        protected Request $request,
    ) {}

    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? 'unknown';

        $this->loginHistory->recordFailure(
            is_string($email) ? $email : 'unknown',
            $this->request,
            'invalid_credentials'
        );

        ActivityLogger::log('authentication', 'login_failed', null, [
            'email' => $email,
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        ActivityLogger::log('authentication', 'logout', $event->user, [
            'email' => $event->user->email,
        ]);
    }
}

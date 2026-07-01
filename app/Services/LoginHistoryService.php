<?php

namespace App\Services;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;

class LoginHistoryService
{
    public function recordSuccess(User $user, Request $request): LoginHistory
    {
        return LoginHistory::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'success' => true,
            'created_at' => now(),
        ]);
    }

    public function recordFailure(string $email, Request $request, ?string $reason = null): LoginHistory
    {
        $user = User::where('email', $email)->first();

        return LoginHistory::create([
            'user_id' => $user?->id,
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'success' => false,
            'failure_reason' => $reason,
            'created_at' => now(),
        ]);
    }
}

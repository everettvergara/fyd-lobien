<?php

namespace App\Modules\Authentication\Controllers;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Notifications\AccountActivatedNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('admin.dashboard');
        }

        return view('authentication::auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('admin.dashboard');
        }

        if ($request->user()->markEmailAsVerified()) {
            $user = $request->user();

            if ($user->status === UserStatus::PendingVerification) {
                $user->update(['status' => UserStatus::Active]);
                $user->notify(new AccountActivatedNotification);
            }

            event(new Verified($user));
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Your email has been verified. Welcome!');
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('admin.dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'A new verification link has been sent to your email address.');
    }
}

<?php

namespace App\Modules\Authentication\Controllers;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AccountActivatedNotification;
use Illuminate\Auth\Events\Verified;
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

    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        if (! $request->hasValidSignature(absolute: false)) {
            abort(403, 'Invalid or expired verification link.');
        }

        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('admin.login')
                ->with('success', 'Email already verified. You may sign in.');
        }

        if ($user->markEmailAsVerified()) {
            if ($user->status === UserStatus::PendingVerification) {
                $user->update(['status' => UserStatus::Active]);
                $user->notify(new AccountActivatedNotification);
            }

            event(new Verified($user));
        }

        return redirect()->route('admin.login')
            ->with('success', 'Your email has been verified. You may now sign in.');
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

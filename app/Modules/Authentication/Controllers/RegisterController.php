<?php

namespace App\Modules\Authentication\Controllers;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Authentication\Requests\RegisterRequest;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (! config('fyd.registration_enabled', true)) {
            return redirect()->route('admin.login')
                ->with('error', 'Registration is currently disabled.');
        }

        return view('authentication::auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'status' => UserStatus::PendingVerification,
        ]);

        event(new Registered($user));

        $user->notify(new WelcomeNotification);

        return redirect()->route('admin.login')
            ->with('success', 'Registration successful! Please check your email to verify your account.');
    }
}

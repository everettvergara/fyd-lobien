<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Requests\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('authentication::profile.show', [
            'user' => auth()->user(),
        ]);
    }

    public function edit(): View
    {
        return view('authentication::profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $emailChanged = $user->email !== $request->email;

        $user->fill($request->validated());

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();

            return redirect()->route('admin.profile.show')
                ->with('success', 'Profile updated. Please verify your new email address.');
        }

        return redirect()->route('admin.profile.show')
            ->with('success', 'Profile updated successfully.');
    }
}

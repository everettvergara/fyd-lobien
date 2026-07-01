<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Requests\ChangePasswordRequest;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ChangePasswordController extends Controller
{
    public function show(): View
    {
        return view('authentication::profile.change-password');
    }

    public function update(ChangePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        $request->user()->notify(new PasswordChangedNotification);

        return redirect()->route('admin.profile.show')
            ->with('success', 'Password changed successfully.');
    }
}

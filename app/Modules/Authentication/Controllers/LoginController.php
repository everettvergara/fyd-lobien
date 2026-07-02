<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Requests\LoginRequest;
use App\Services\ActivityLogger;
use App\Services\LoginHistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('authentication::auth.login');
    }

    public function store(LoginRequest $request, LoginHistoryService $loginHistory): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $user->update([
            'last_login_at' => now(),
        ]);

        $loginHistory->recordSuccess($user, $request);

        ActivityLogger::log('authentication', 'login', $user, [
            'email' => $user->email,
        ]);

        $destination = $user->hasPermission('dashboard.view')
            ? route('admin.dashboard')
            : route('admin.access.pending');

        return redirect()->intended($destination);
    }
}

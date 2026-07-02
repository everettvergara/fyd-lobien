<?php

namespace App\Http\Middleware;

use App\Services\AuthConfigService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrationEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(AuthConfigService::class)->registrationEnabled()) {
            if ($request->expectsJson()) {
                abort(403, 'Registration is currently disabled.');
            }

            return redirect()->route('admin.login')
                ->with('error', 'Registration is currently disabled.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPanelAccess
{
    /**
     * Routes accessible before an administrator assigns a role.
     *
     * @var array<int, string>
     */
    protected array $except = [
        'admin.access.pending',
        'admin.logout',
        'admin.verification.notice',
        'admin.verification.send',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasVerifiedEmail() && ! $user->hasPermission('dashboard.view')) {
            if (! $request->routeIs(...$this->except)) {
                return redirect()->route('admin.access.pending');
            }
        }

        return $next($request);
    }
}

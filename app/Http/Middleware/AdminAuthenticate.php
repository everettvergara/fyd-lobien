<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * Authentication logic will be implemented in Phase 2.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}

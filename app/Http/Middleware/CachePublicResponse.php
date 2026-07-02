<?php

namespace App\Http\Middleware;

use App\Modules\Cache\Services\PublicCacheService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CachePublicResponse
{
    public function __construct(
        protected PublicCacheService $cache,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->cache->shouldCache($request)) {
            return $next($request);
        }

        if ($cached = $this->cache->get($request)) {
            return $cached;
        }

        $response = $next($request);

        if ($this->cache->shouldCache($request)) {
            $this->cache->put($request, $response);
        }

        return $response;
    }
}

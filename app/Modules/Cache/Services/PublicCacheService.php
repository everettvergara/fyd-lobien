<?php

namespace App\Modules\Cache\Services;

use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PublicCacheService
{
    public const KEY_PREFIX = 'public.response.';

    protected const ALLOWED_ROUTES = ['page.show'];

    public function __construct(
        protected SettingsService $settings,
    ) {}

    public function isEnabled(): bool
    {
        return (bool) $this->settings->get('cache', 'enabled', true);
    }

    public function ttl(): int
    {
        $days = (int) $this->settings->get('cache', 'ttl_days', 1);

        return max(1, $days) * 86400;
    }

    public function shouldCache(Request $request): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->user()) {
            return false;
        }

        $adminPrefix = trim(config('fyd.admin.prefix', 'admin'), '/');

        if ($request->is($adminPrefix, $adminPrefix.'/*', 'search', 'sitemap.xml', 'robots.txt', 'up')) {
            return false;
        }

        $routeName = $request->route()?->getName();

        return in_array($routeName, self::ALLOWED_ROUTES, true);
    }

    public function cacheKey(Request $request): string
    {
        $inertia = $request->headers->has('X-Inertia') ? 'inertia' : 'full';

        return self::KEY_PREFIX.hash('sha256', $request->getMethod().'|'.$request->getRequestUri().'|'.$inertia);
    }

    public function get(Request $request): ?Response
    {
        $payload = Cache::get($this->cacheKey($request));

        if (! is_array($payload) || ! isset($payload['content'], $payload['status'])) {
            return null;
        }

        return response($payload['content'], (int) $payload['status'], $payload['headers'] ?? []);
    }

    public function put(Request $request, Response $response): void
    {
        if ($response->getStatusCode() !== 200) {
            return;
        }

        $key = $this->cacheKey($request);

        Cache::put($key, [
            'content' => $response->getContent(),
            'status' => $response->getStatusCode(),
            'headers' => $response->headers->all(),
        ], $this->ttl());

        $this->trackKey($key);
    }

    public function clearAll(): int
    {
        $count = $this->clearIndexedKeys();

        $store = config('cache.default', 'database');

        if (config("cache.stores.{$store}.driver") === 'database') {
            $count += $this->clearDatabaseKeys();
        }

        return $count;
    }

    protected function trackKey(string $key): void
    {
        $indexKey = self::KEY_PREFIX.'_index';
        $index = Cache::get($indexKey, []);

        if (! in_array($key, $index, true)) {
            $index[] = $key;
            Cache::forever($indexKey, $index);
        }
    }

    protected function clearIndexedKeys(): int
    {
        $indexKey = self::KEY_PREFIX.'_index';
        $index = Cache::get($indexKey, []);
        $count = 0;

        foreach ($index as $key) {
            Cache::forget($key);
            $count++;
        }

        Cache::forget($indexKey);

        return $count;
    }

    protected function clearDatabaseKeys(): int
    {
        $prefix = (string) config('cache.prefix', '');
        $table = config('cache.stores.database.table', 'cache');

        return DB::table($table)
            ->where('key', 'like', $prefix.self::KEY_PREFIX.'%')
            ->delete();
    }
}

<?php

namespace App\Modules\Cache\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cache\Models\CacheSettings;
use App\Modules\Cache\Requests\UpdateCacheSettingsRequest;
use App\Modules\Cache\Services\PublicCacheService;
use App\Services\ActivityLogger;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CacheSettingsController extends Controller
{
    public function __construct(
        protected SettingsService $settings,
        protected PublicCacheService $publicCache,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', CacheSettings::class);

        $settings = [
            'enabled' => $this->settings->get('cache', 'enabled', true),
            'ttl_days' => $this->settings->get('cache', 'ttl_days', 1),
        ];

        return view('cache::cache.index', compact('settings'));
    }

    public function update(UpdateCacheSettingsRequest $request): RedirectResponse
    {
        $this->authorize('update', CacheSettings::class);

        $enabled = filter_var($request->validated('enabled'), FILTER_VALIDATE_BOOLEAN);
        $this->settings->set('cache', 'enabled', $enabled ? 'true' : 'false', 'boolean');
        $this->settings->set('cache', 'ttl_days', (string) $request->validated('ttl_days'));

        ActivityLogger::log('cache', 'settings_updated');

        return redirect()->route('admin.cache.index')->with('success', 'Cache settings updated successfully.');
    }

    public function clear(): RedirectResponse
    {
        $this->authorize('update', CacheSettings::class);

        $count = $this->publicCache->clearAll();
        ActivityLogger::log('cache', 'cleared', null, ['entries_removed' => $count]);

        return redirect()->route('admin.cache.index')->with('success', 'Public page cache cleared successfully.');
    }
}

<?php

namespace App\Http\Middleware;

use App\Services\NavigationService;
use App\Services\Theme\ThemeService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $nav = app(NavigationService::class);
        $theme = app(ThemeService::class);
        $manifest = $theme->activeManifest();

        return [
            ...parent::share($request),
            'app' => $nav->siteInfo(),
            'navigation' => [
                'header' => $nav->header(),
                'footer' => $nav->footer(),
            ],
            'theme' => [
                'slug' => $theme->activeSlug(),
                'name' => $manifest['name'] ?? $theme->activeSlug(),
            ],
            'recaptcha' => [
                'enabled' => config('recaptcha.enabled'),
                'siteKey' => config('recaptcha.site_key'),
            ],
        ];
    }
}

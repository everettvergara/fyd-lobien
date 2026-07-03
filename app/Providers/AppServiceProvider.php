<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationEvents;
use App\Services\AuthConfigService;
use App\Services\MailConfigService;
use App\Services\PasswordPolicyService;
use App\Services\SettingsService;
use App\Services\Theme\ThemeService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(\App\Services\Public\PublicBlockRegistry::class);
        $this->app->singleton(\App\Services\Public\PageRenderService::class);

        $this->app->singleton('inertia.view-finder', function ($app) {
            $slug = config('fyd.themes.default', 'fyd-default');

            try {
                $slug = $app->make(ThemeService::class)->activeSlug();
            } catch (\Throwable) {
                // Fall back during early bootstrap.
            }

            $themePagesPath = base_path("themes/{$slug}/js/Pages");

            if (! is_dir($themePagesPath)) {
                $themePagesPath = base_path('themes/fyd-default/js/Pages');
            }

            return new \Illuminate\View\FileViewFinder(
                $app['files'],
                [$themePagesPath],
                $app['config']->get('inertia.pages.extensions', ['vue'])
            );
        });
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Event::listen(Failed::class, [LogAuthenticationEvents::class, 'handleFailed']);
        Event::listen(Logout::class, [LogAuthenticationEvents::class, 'handleLogout']);
        Event::listen(Registered::class, SendEmailVerificationNotification::class);

        if (! app()->environment('local') && ($rootUrl = config('app.url'))) {
            URL::forceRootUrl($rootUrl);
        }

        app(PasswordPolicyService::class)->apply();
        app(AuthConfigService::class)->apply();
        app(MailConfigService::class)->apply();
    }
}

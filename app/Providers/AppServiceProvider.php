<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationEvents;
use App\Services\AuthConfigService;
use App\Services\MailConfigService;
use App\Services\PasswordPolicyService;
use App\Services\SettingsService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Event::listen(Failed::class, [LogAuthenticationEvents::class, 'handleFailed']);
        Event::listen(Logout::class, [LogAuthenticationEvents::class, 'handleLogout']);

        app(PasswordPolicyService::class)->apply();
        app(AuthConfigService::class)->apply();
        app(MailConfigService::class)->apply();
    }
}

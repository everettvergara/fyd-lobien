<?php

namespace App\Providers;

use App\Http\Controllers\Public\PublicPageController;
use App\Http\Controllers\Public\SearchController;
use App\Framework\MenuRegistry;
use App\Framework\ModuleRegistry;
use App\Services\Module\ModuleManagerService;
use App\Services\Module\ModuleRuntimeService;
use App\Services\NavigationService;
use App\Support\CmsVersion;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class);
        $this->app->singleton(MenuRegistry::class);
        $this->app->singleton(ModuleManagerService::class);
        $this->app->singleton(ModuleRuntimeService::class);
    }

    public function boot(): void
    {
        $bootable = app(ModuleManagerService::class)->bootableModuleNames();

        app(ModuleRuntimeService::class)->discoverAndBoot($bootable);
        $this->loadModuleRoutes($bootable);
        $this->registerPublicPageCatchAll();
        $this->loadModuleViews($bootable);
        $this->loadModuleMigrations($bootable);
        $this->registerModuleServiceProviders($bootable);
        $this->registerModuleCommands($bootable);

        View::composer('admin.layouts.partials.sidebar', function ($view) {
            $panels = app(MenuRegistry::class)->panelsFor(auth()->user());
            $view->with('coreMenuSections', $panels['core']);
            $view->with('businessMenuSections', $panels['business']);
            $view->with('menuSections', $panels['core']);
        });

        View::share('cmsVersion', CmsVersion::info());

        View::composer([
            'admin.layouts.app',
            'admin.layouts.auth',
            'admin.layouts.partials.sidebar',
            'admin.layouts.partials.version-footer',
            'app',
        ], function ($view) {
            $view->with('app', app(NavigationService::class)->siteInfo());
        });
    }

    /**
     * @param  array<int, string>  $bootable
     */
    protected function loadModuleRoutes(array $bootable): void
    {
        $modulesPath = config('modules.path');

        foreach ($bootable as $module) {
            $adminRoutes = "{$modulesPath}/{$module}/Routes/admin.php";

            if (file_exists($adminRoutes)) {
                Route::middleware(array_merge(config('fyd.admin.middleware'), ['admin.access']))
                    ->prefix(config('fyd.admin.prefix'))
                    ->name('admin.')
                    ->group($adminRoutes);
            }

            $webRoutes = "{$modulesPath}/{$module}/Routes/web.php";

            if (file_exists($webRoutes)) {
                Route::middleware('web')
                    ->group($webRoutes);
            }
        }
    }

    protected function registerPublicPageCatchAll(): void
    {
        Route::middleware('web')->group(function () {
            Route::get('/search', [SearchController::class, 'index'])->name('search');
            Route::post('/search', [SearchController::class, 'store'])->name('search.submit');

            Route::get('/{path?}', [PublicPageController::class, 'show'])
                ->where('path', '^(?!(?:api|admin|newsletters(?:/|$)|careers/.+)).*$')
                ->name('page.show');
        });
    }

    /**
     * @param  array<int, string>  $bootable
     */
    protected function loadModuleViews(array $bootable): void
    {
        $modulesPath = config('modules.path');

        foreach ($bootable as $module) {
            $viewsPath = "{$modulesPath}/{$module}/Views";

            if (is_dir($viewsPath)) {
                $this->loadViewsFrom($viewsPath, strtolower($module));
            }
        }
    }

    /**
     * @param  array<int, string>  $bootable
     */
    protected function loadModuleMigrations(array $bootable): void
    {
        $modulesPath = config('modules.path');

        foreach ($bootable as $module) {
            foreach (["{$modulesPath}/{$module}/Database/Migrations", "{$modulesPath}/{$module}/Migrations"] as $migrationsPath) {
                if (is_dir($migrationsPath)) {
                    $this->loadMigrationsFrom($migrationsPath);
                }
            }
        }
    }

    /**
     * @param  array<int, string>  $bootable
     */
    protected function registerModuleServiceProviders(array $bootable): void
    {
        foreach ($bootable as $moduleName) {
            $providerClass = "App\\Modules\\{$moduleName}\\{$moduleName}ServiceProvider";

            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }

    /**
     * @param  array<int, string>  $bootable
     */
    protected function registerModuleCommands(array $bootable): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $manager = app(ModuleManagerService::class);
        $commands = [];

        foreach ($bootable as $moduleName) {
            $module = $manager->resolveModule($moduleName);

            if ($module === null) {
                continue;
            }

            $commands = array_merge($commands, $module->commands());
        }

        if ($commands !== []) {
            $this->commands(array_values(array_unique($commands)));
        }
    }
}

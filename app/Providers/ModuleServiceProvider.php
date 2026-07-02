<?php

namespace App\Providers;

use App\Framework\MenuRegistry;
use App\Framework\ModuleRegistry;
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
    }

    public function boot(): void
    {
        $this->discoverModules();
        $this->loadModuleRoutes();
        $this->loadModuleViews();
        $this->loadModuleMigrations();

        View::composer('admin.layouts.partials.sidebar', function ($view) {
            $view->with('menuSections', app(MenuRegistry::class)->sectionsFor(auth()->user()));
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

    protected function discoverModules(): void
    {
        $registry = app(ModuleRegistry::class);
        $menuRegistry = app(MenuRegistry::class);
        $modulesPath = config('modules.path');

        foreach (config('modules.enabled', []) as $module) {
            $moduleFile = "{$modulesPath}/{$module}/Module.php";

            if (! file_exists($moduleFile)) {
                continue;
            }

            $class = "App\\Modules\\{$module}\\Module";

            if (class_exists($class)) {
                $registry->register(app($class));
            }
        }

        $registry->bootPolicies();
        $registry->bootMenus($menuRegistry);
    }

    protected function loadModuleRoutes(): void
    {
        $modulesPath = config('modules.path');

        foreach (config('modules.enabled', []) as $module) {
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

    protected function loadModuleViews(): void
    {
        $modulesPath = config('modules.path');

        foreach (config('modules.enabled', []) as $module) {
            $viewsPath = "{$modulesPath}/{$module}/Views";

            if (is_dir($viewsPath)) {
                $this->loadViewsFrom($viewsPath, strtolower($module));
            }
        }
    }

    protected function loadModuleMigrations(): void
    {
        $modulesPath = config('modules.path');

        foreach (config('modules.enabled', []) as $module) {
            foreach (["{$modulesPath}/{$module}/Database/Migrations", "{$modulesPath}/{$module}/Migrations"] as $migrationsPath) {
                if (is_dir($migrationsPath)) {
                    $this->loadMigrationsFrom($migrationsPath);
                }
            }
        }
    }
}

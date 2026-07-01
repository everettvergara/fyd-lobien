<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadModuleRoutes();
        $this->loadModuleViews();
        $this->loadModuleMigrations();
    }

    protected function loadModuleRoutes(): void
    {
        $modulesPath = config('modules.path');

        foreach (config('modules.enabled', []) as $module) {
            $adminRoutes = "{$modulesPath}/{$module}/Routes/admin.php";

            if (file_exists($adminRoutes)) {
                Route::middleware(config('fyd.admin.middleware'))
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
            $migrationsPath = "{$modulesPath}/{$module}/Migrations";

            if (is_dir($migrationsPath)) {
                $this->loadMigrationsFrom($migrationsPath);
            }
        }
    }
}

<?php

namespace App\Services\Module;

use App\Framework\MenuRegistry;
use App\Framework\Module;
use App\Framework\ModuleRegistry;
use Illuminate\Support\Facades\Gate;

class ModuleRuntimeService
{
    /**
     * @param  array<int, string>  $bootable
     */
    public function discoverAndBoot(array $bootable): void
    {
        $registry = app(ModuleRegistry::class);
        $menuRegistry = app(MenuRegistry::class);
        $modulesPath = config('modules.path');

        foreach ($bootable as $moduleName) {
            $moduleFile = "{$modulesPath}/{$moduleName}/Module.php";

            if (! file_exists($moduleFile)) {
                continue;
            }

            $class = "App\\Modules\\{$moduleName}\\Module";

            if (class_exists($class)) {
                $registry->register(app($class));
            }
        }

        $registry->bootPolicies();
        $registry->bootMenus($menuRegistry);
        $registry->bootPublicBlocks(app(\App\Services\Public\PublicBlockRegistry::class));
    }

    public function registerModule(Module $module): void
    {
        $registry = app(ModuleRegistry::class);
        $menuRegistry = app(MenuRegistry::class);
        $blockRegistry = app(\App\Services\Public\PublicBlockRegistry::class);

        $registry->register($module);

        foreach ($module->policies() as $model => $policy) {
            Gate::policy($model, $policy);
        }

        foreach ($module->menuItems() as $item) {
            $menuRegistry->register($item);
        }

        foreach ($module->publicBlocks() as $block) {
            $blockRegistry->register($block);
        }

        $providerClass = 'App\\Modules\\'.$module->name().'\\'.$module->name().'ServiceProvider';

        if (class_exists($providerClass)) {
            app()->register($providerClass);
        }
    }

    public function reloadBootableModules(): void
    {
        app(ModuleRegistry::class)->reset();
        app(MenuRegistry::class)->reset();

        $this->discoverAndBoot(app(ModuleManagerService::class)->bootableModuleNames());
    }
}

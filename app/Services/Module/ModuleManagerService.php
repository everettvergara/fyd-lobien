<?php

namespace App\Services\Module;

use App\Framework\MenuRegistry;
use App\Framework\Module;
use App\Framework\ModuleRegistry;
use App\Models\InstalledModule;
use App\Services\ActivityLogger;
use App\Services\Module\ModuleManifestService;
use App\Services\Module\ModuleRuntimeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class ModuleManagerService
{
    public function __construct(
        protected ModuleMigrationService $migrations,
        protected ModuleManifestService $manifests,
        protected PermissionSyncService $permissions,
        protected ModuleRuntimeService $runtime,
    ) {}

    /**
     * @return array<int, string>
     */
    public function coreModuleNames(): array
    {
        return config('modules.core', []);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listForAdmin(): Collection
    {
        $rows = collect();

        foreach ($this->discoverInstallable() as $module) {
            $name = $module->name();
            $installed = InstalledModule::where('name', $name)->first();
            $status = $installed?->status ?? 'available';

            if ($installed === null && ! $this->moduleDirectoryExists($name)) {
                continue;
            }

            $rows->push($this->rowFromModule($module, $status, $installed));
        }

        return $rows->sortBy([
            fn (array $row) => match ($row['status']) {
                'installed' => 0,
                'disabled' => 1,
                default => 2,
            },
            'group_sort',
            'name',
        ])->values();
    }

    /**
     * @return array<int, Module>
     */
    public function discoverInstallable(): array
    {
        $modules = [];
        $core = $this->coreModuleNames();
        $path = config('modules.path');

        if (! is_dir($path)) {
            return [];
        }

        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (in_array($entry, $core, true)) {
                continue;
            }

            $module = $this->resolveModule($entry);

            if ($module !== null && $module->isInstallable()) {
                $modules[$module->name()] = $module;
            }
        }

        return array_values($modules);
    }

    /**
     * @return array<int, Module>
     */
    public function activeInstallableModules(): array
    {
        if (! Schema::hasTable('installed_modules')) {
            return [];
        }

        $modules = [];

        foreach (InstalledModule::where('status', InstalledModule::STATUS_INSTALLED)->pluck('name') as $name) {
            $module = $this->resolveModule($name);

            if ($module !== null && $module->isInstallable()) {
                $modules[] = $module;
            }
        }

        return $modules;
    }

    /**
     * @return array<int, string>
     */
    public function bootableModuleNames(): array
    {
        $names = $this->coreModuleNames();

        if (Schema::hasTable('installed_modules')) {
            $names = array_merge(
                $names,
                InstalledModule::where('status', InstalledModule::STATUS_INSTALLED)->pluck('name')->all(),
            );
        }

        return array_values(array_unique($names));
    }

    public function resolveModule(string $folderName): ?Module
    {
        $moduleFile = config('modules.path')."/{$folderName}/Module.php";

        if (! is_file($moduleFile)) {
            return null;
        }

        $class = "App\\Modules\\{$folderName}\\Module";

        if (! class_exists($class)) {
            return null;
        }

        $module = app($class);

        return $module instanceof Module ? $module : null;
    }

    public function install(string $name, ?int $userId = null): void
    {
        $module = $this->requireInstallableModule($name);

        $this->manifests->validateForInstall($module);

        if (! $this->moduleDirectoryExists($name)) {
            throw new InvalidArgumentException("Module folder [{$name}] was not found under app/Modules.");
        }

        foreach ($this->migrations->relativeMigrationPaths($name) as $path) {
            Artisan::call('migrate', [
                '--path' => $path,
                '--force' => true,
            ]);
        }

        foreach ($module->seeders() as $seederClass) {
            Artisan::call('db:seed', [
                '--class' => $seederClass,
                '--force' => true,
            ]);
        }

        $this->permissions->syncModule($module);
        $this->permissions->grantSuperAdmin($module);

        InstalledModule::updateOrCreate(
            ['name' => $module->name()],
            [
                'status' => InstalledModule::STATUS_INSTALLED,
                'version' => $module->version(),
                'installed_at' => now(),
                'disabled_at' => null,
                'metadata' => [
                    'group' => $module->group(),
                    'features' => $module->features(),
                ],
            ],
        );

        app(ModuleRuntimeService::class)->registerModule($module);

        $this->clearCaches(false);

        ActivityLogger::log('modules', 'installed', null, [
            'module' => $module->name(),
            'version' => $module->version(),
            'user_id' => $userId,
        ]);
    }

    public function disable(string $name, ?int $userId = null): void
    {
        $record = $this->requireInstalledRecord($name);

        $record->update([
            'status' => InstalledModule::STATUS_DISABLED,
            'disabled_at' => now(),
        ]);

        $this->clearCaches();

        ActivityLogger::log('modules', 'disabled', null, [
            'module' => $name,
            'user_id' => $userId,
        ]);
    }

    public function enable(string $name, ?int $userId = null): void
    {
        $module = $this->requireInstallableModule($name);
        $record = $this->requireInstalledRecord($name);

        $this->permissions->syncModule($module);
        $this->permissions->grantSuperAdmin($module);

        $record->update([
            'status' => InstalledModule::STATUS_INSTALLED,
            'disabled_at' => null,
        ]);

        app(ModuleRuntimeService::class)->registerModule($module);

        $this->clearCaches(false);

        ActivityLogger::log('modules', 'enabled', null, [
            'module' => $name,
            'user_id' => $userId,
        ]);
    }

    public function uninstall(string $name, ?int $userId = null): void
    {
        $module = $this->requireInstallableModule($name);
        $record = InstalledModule::where('name', $name)->first();

        if ($record === null) {
            throw new InvalidArgumentException("Module [{$name}] is not installed.");
        }

        $module->uninstall();

        app(ModulePageBlockCleanupService::class)->removeBlocksForModule($module);

        foreach (array_reverse($this->migrations->relativeMigrationPaths($name)) as $path) {
            Artisan::call('migrate:rollback', [
                '--path' => $path,
                '--force' => true,
            ]);
        }

        $this->permissions->pruneModule($module);

        $record->delete();

        $this->clearCaches();

        ActivityLogger::log('modules', 'uninstalled', null, [
            'module' => $name,
            'user_id' => $userId,
        ]);
    }

    protected function requireInstallableModule(string $name): Module
    {
        if (in_array($name, $this->coreModuleNames(), true)) {
            throw new InvalidArgumentException("Module [{$name}] is a core module and cannot be lifecycle-managed.");
        }

        $module = $this->resolveModule($name);

        if ($module === null || ! $module->isInstallable()) {
            throw new InvalidArgumentException("Installable module [{$name}] was not found.");
        }

        return $module;
    }

    protected function requireInstalledRecord(string $name): InstalledModule
    {
        $record = InstalledModule::where('name', $name)->first();

        if ($record === null) {
            throw new InvalidArgumentException("Module [{$name}] is not installed.");
        }

        return $record;
    }

    protected function moduleDirectoryExists(string $name): bool
    {
        return is_dir(config('modules.path').'/'.$name);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rowFromModule(Module $module, string $status, ?InstalledModule $installed): array
    {
        return [
            'name' => $module->name(),
            'slug' => Str::slug($module->name()),
            'group' => $module->group(),
            'group_sort' => $module->groupSort(),
            'version' => $module->version(),
            'description' => $module->description(),
            'status' => $status,
            'features' => $module->features(),
            'requires_core' => $this->manifests->requiredCoreModules($module),
            'compatible' => $this->manifests->isCompatible($module->manifest()['fyd_cms'] ?? null),
            'installed_at' => $installed?->installed_at,
            'disabled_at' => $installed?->disabled_at,
        ];
    }

    protected function clearCaches(bool $reload = true): void
    {
        if (! app()->environment('testing')) {
            Artisan::call('optimize:clear');
        }

        if ($reload) {
            app(ModuleRuntimeService::class)->reloadBootableModules();
        }
    }
}

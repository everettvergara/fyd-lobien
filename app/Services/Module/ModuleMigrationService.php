<?php

namespace App\Services\Module;

use App\Framework\Module;

class ModuleMigrationService
{
    /**
     * @return array<int, string>
     */
    public function migrationPaths(string $moduleName): array
    {
        $base = config('modules.path').'/'.$moduleName;
        $paths = [];

        foreach (["{$base}/Database/Migrations", "{$base}/Migrations"] as $path) {
            if (is_dir($path)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    public function hasMigrations(string $moduleName): bool
    {
        return $this->migrationPaths($moduleName) !== [];
    }

    /**
     * @return array<int, string>
     */
    public function relativeMigrationPaths(string $moduleName): array
    {
        return array_map(
            fn (string $path) => str_replace(base_path().DIRECTORY_SEPARATOR, '', $path),
            $this->migrationPaths($moduleName),
        );
    }
}

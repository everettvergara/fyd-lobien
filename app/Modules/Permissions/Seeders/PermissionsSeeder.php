<?php

namespace App\Modules\Permissions\Seeders;

use App\Framework\ModuleRegistry;
use App\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * Syncs module permissions from ModuleRegistry into the permissions table.
 *
 * @see docs/SEEDING.md
 */
class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $registry = app(ModuleRegistry::class);
        $permissions = [];

        foreach ($registry->all() as $module) {
            $permissions = array_merge($permissions, $module->permissions());
        }

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['module'].'.'.$permission['action']],
                [
                    'module' => $permission['module'],
                    'action' => $permission['action'],
                    'display_name' => $permission['display_name'],
                ]
            );
        }
    }
}

<?php

namespace App\Services\Module;

use App\Framework\Module;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class PermissionSyncService
{
    public function syncModule(Module $module): void
    {
        foreach ($module->permissions() as $permission) {
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

    public function pruneModule(Module $module): void
    {
        $names = collect($module->permissions())
            ->map(fn (array $permission) => $permission['module'].'.'.$permission['action'])
            ->all();

        if ($names === []) {
            return;
        }

        DB::table('permission_role')
            ->whereIn('permission_id', Permission::whereIn('name', $names)->pluck('id'))
            ->delete();

        Permission::whereIn('name', $names)->delete();
    }

    public function grantSuperAdmin(Module $module): void
    {
        $role = Role::where('name', 'super_administrator')->first();

        if ($role === null) {
            return;
        }

        $ids = collect($module->permissions())
            ->map(fn (array $permission) => Permission::where('name', $permission['module'].'.'.$permission['action'])->value('id'))
            ->filter()
            ->all();

        if ($ids !== []) {
            $role->permissions()->syncWithoutDetaching($ids);
        }
    }
}

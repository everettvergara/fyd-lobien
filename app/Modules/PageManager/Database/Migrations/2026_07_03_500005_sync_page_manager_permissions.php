<?php

use App\Models\Permission;
use App\Models\Role;
use App\Modules\PageManager\Module as PageManagerModule;
use App\Services\Module\PermissionSyncService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $module = app(PageManagerModule::class);
        $sync = app(PermissionSyncService::class);

        $sync->syncModule($module);
        $sync->grantSuperAdmin($module);

        $permissionNames = collect($module->permissions())
            ->map(fn (array $permission) => $permission['module'].'.'.$permission['action'])
            ->all();

        $permissionIds = Permission::query()
            ->whereIn('name', $permissionNames)
            ->pluck('id')
            ->all();

        if ($permissionIds === []) {
            return;
        }

        foreach (['administrator', 'editor'] as $roleName) {
            $role = Role::query()->where('name', $roleName)->first();

            if ($role !== null) {
                $role->permissions()->syncWithoutDetaching($permissionIds);
            }
        }
    }

    public function down(): void
    {
        // Permissions remain registered for Page Manager.
    }
};

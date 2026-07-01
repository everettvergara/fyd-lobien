<?php

namespace App\Modules\Permissions\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['module' => 'dashboard', 'action' => 'view', 'display_name' => 'View Dashboard'],
            ['module' => 'users', 'action' => 'view', 'display_name' => 'View Users'],
            ['module' => 'users', 'action' => 'create', 'display_name' => 'Create Users'],
            ['module' => 'users', 'action' => 'edit', 'display_name' => 'Edit Users'],
            ['module' => 'users', 'action' => 'delete', 'display_name' => 'Delete Users'],
            ['module' => 'roles', 'action' => 'view', 'display_name' => 'View Roles'],
            ['module' => 'roles', 'action' => 'create', 'display_name' => 'Create Roles'],
            ['module' => 'roles', 'action' => 'edit', 'display_name' => 'Edit Roles'],
            ['module' => 'roles', 'action' => 'delete', 'display_name' => 'Delete Roles'],
            ['module' => 'permissions', 'action' => 'view', 'display_name' => 'View Permissions'],
            ['module' => 'pages', 'action' => 'view', 'display_name' => 'View Pages'],
            ['module' => 'pages', 'action' => 'create', 'display_name' => 'Create Pages'],
            ['module' => 'pages', 'action' => 'edit', 'display_name' => 'Edit Pages'],
            ['module' => 'pages', 'action' => 'delete', 'display_name' => 'Delete Pages'],
            ['module' => 'pages', 'action' => 'publish', 'display_name' => 'Publish Pages'],
            ['module' => 'posts', 'action' => 'view', 'display_name' => 'View Posts'],
            ['module' => 'posts', 'action' => 'create', 'display_name' => 'Create Posts'],
            ['module' => 'posts', 'action' => 'edit', 'display_name' => 'Edit Posts'],
            ['module' => 'posts', 'action' => 'delete', 'display_name' => 'Delete Posts'],
            ['module' => 'posts', 'action' => 'publish', 'display_name' => 'Publish Posts'],
            ['module' => 'banners', 'action' => 'view', 'display_name' => 'View Banners'],
            ['module' => 'banners', 'action' => 'create', 'display_name' => 'Create Banners'],
            ['module' => 'banners', 'action' => 'edit', 'display_name' => 'Edit Banners'],
            ['module' => 'banners', 'action' => 'delete', 'display_name' => 'Delete Banners'],
            ['module' => 'banners', 'action' => 'publish', 'display_name' => 'Publish Banners'],
            ['module' => 'menus', 'action' => 'view', 'display_name' => 'View Menus'],
            ['module' => 'menus', 'action' => 'create', 'display_name' => 'Create Menus'],
            ['module' => 'menus', 'action' => 'edit', 'display_name' => 'Edit Menus'],
            ['module' => 'menus', 'action' => 'delete', 'display_name' => 'Delete Menus'],
            ['module' => 'media', 'action' => 'view', 'display_name' => 'View Media'],
            ['module' => 'media', 'action' => 'create', 'display_name' => 'Create Media'],
            ['module' => 'media', 'action' => 'edit', 'display_name' => 'Edit Media'],
            ['module' => 'media', 'action' => 'delete', 'display_name' => 'Delete Media'],
            ['module' => 'settings', 'action' => 'view', 'display_name' => 'View Settings'],
            ['module' => 'settings', 'action' => 'edit', 'display_name' => 'Edit Settings'],
            ['module' => 'activity_log', 'action' => 'view', 'display_name' => 'View Audit Logs'],
            ['module' => 'sessions', 'action' => 'view', 'display_name' => 'View Sessions'],
            ['module' => 'sessions', 'action' => 'delete', 'display_name' => 'Revoke Sessions'],
        ];

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

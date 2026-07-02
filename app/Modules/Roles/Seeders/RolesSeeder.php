<?php

namespace App\Modules\Roles\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds system roles and assigns permissions for new installs.
 *
 * @see docs/SEEDING.md
 */
class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $allPermissions = Permission::pluck('id', 'name');

        $roles = [
            'super_administrator' => [
                'display_name' => 'Super Administrator',
                'description' => 'Full system access including permission management.',
                'is_system' => true,
                'permissions' => $allPermissions->keys()->all(),
            ],
            'administrator' => [
                'display_name' => 'Administrator',
                'description' => 'Full access except permission management.',
                'is_system' => true,
                'permissions' => $allPermissions->keys()->reject(fn ($name) => str_starts_with($name, 'permissions.'))->all(),
            ],
            'editor' => [
                'display_name' => 'Editor',
                'description' => 'Can manage and publish content.',
                'is_system' => true,
                'permissions' => $allPermissions->keys()->filter(fn ($name) => in_array(explode('.', $name)[0], ['dashboard', 'content', 'content_types', 'banners', 'menus', 'media', 'provinces', 'cities', 'seo']) || $name === 'dashboard.view')->all(),
            ],
            'author' => [
                'display_name' => 'Author',
                'description' => 'Can create and edit content without publishing.',
                'is_system' => true,
                'permissions' => $allPermissions->keys()->filter(function ($name) {
                    $parts = explode('.', $name);

                    return (in_array($parts[0], ['dashboard', 'content', 'content_types', 'banners', 'media', 'provinces', 'cities'])
                        && in_array($parts[1] ?? '', ['view', 'create', 'edit']))
                        || ($parts[0] === 'seo' && ($parts[1] ?? '') === 'view');
                })->all(),
            ],
            'viewer' => [
                'display_name' => 'Viewer',
                'description' => 'Read-only access to content and dashboard.',
                'is_system' => true,
                'permissions' => $allPermissions->keys()->filter(fn ($name) => str_ends_with($name, '.view') && ! str_starts_with($name, 'seo.') && ! str_starts_with($name, 'site_reports.'))->all(),
            ],
        ];

        foreach ($roles as $name => $data) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                [
                    'display_name' => $data['display_name'],
                    'description' => $data['description'],
                    'is_system' => $data['is_system'],
                ]
            );

            $permissionIds = collect($data['permissions'])
                ->map(fn ($permName) => $allPermissions[$permName] ?? null)
                ->filter()
                ->values()
                ->all();

            $role->syncPermissions($permissionIds);
        }
    }
}

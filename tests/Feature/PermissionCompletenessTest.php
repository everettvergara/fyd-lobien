<?php

namespace Tests\Feature;

use App\Framework\ModuleRegistry;
use App\Models\Permission;
use App\Modules\Permissions\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionCompletenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_permissions_match_module_registry(): void
    {
        $this->seed(PermissionsSeeder::class);

        $registry = app(ModuleRegistry::class);
        $expected = [];

        foreach ($registry->all() as $module) {
            $expected = array_merge($expected, $module->permissions());
        }

        $expectedNames = collect($expected)
            ->map(fn (array $permission) => $permission['module'].'.'.$permission['action'])
            ->sort()
            ->values();

        $actualNames = Permission::pluck('name')->sort()->values();

        $this->assertEquals($expectedNames->all(), $actualNames->all());
    }

    public function test_each_module_permission_is_persisted_with_display_name(): void
    {
        $this->seed(PermissionsSeeder::class);

        $registry = app(ModuleRegistry::class);

        foreach ($registry->all() as $module) {
            foreach ($module->permissions() as $permission) {
                $this->assertDatabaseHas('permissions', [
                    'name' => $permission['module'].'.'.$permission['action'],
                    'module' => $permission['module'],
                    'action' => $permission['action'],
                    'display_name' => $permission['display_name'],
                ]);
            }
        }
    }
}

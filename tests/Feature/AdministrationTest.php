<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        $this->seed();

        return User::where('email', 'admin@fyd.local')->first();
    }

    public function test_dashboard_displays_user_count(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Users');
    }

    public function test_users_index_requires_permission(): void
    {
        $this->seed();
        $user = User::factory()->create(['status' => UserStatus::Active]);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_users_list(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Users');
        $response->assertSee('admin@fyd.local');
    }

    public function test_admin_can_create_user(): void
    {
        $admin = $this->createAdminUser();
        $editorRole = Role::where('name', 'editor')->first();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New Editor',
            'email' => 'editor@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => UserStatus::Active->value,
            'roles' => [$editorRole->id],
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['email' => 'editor@example.com']);
    }

    public function test_admin_can_view_roles_list(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/roles');

        $response->assertStatus(200);
        $response->assertSee('Super Administrator');
    }

    public function test_admin_can_view_permissions_list(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/permissions');

        $response->assertStatus(200);
        $response->assertSee('users.view');
    }

    public function test_system_roles_cannot_be_deleted(): void
    {
        $admin = $this->createAdminUser();
        $role = Role::where('name', 'super_administrator')->first();

        $response = $this->actingAs($admin)->delete("/admin/roles/{$role->id}");

        $response->assertStatus(403);
    }

    public function test_system_role_permissions_cannot_be_changed(): void
    {
        $admin = $this->createAdminUser();
        $role = Role::where('name', 'super_administrator')->first();
        $originalCount = $role->permissions()->count();

        $response = $this->actingAs($admin)->put("/admin/roles/{$role->id}", [
            'display_name' => $role->display_name,
            'description' => $role->description,
            'permissions' => [],
        ]);

        $response->assertRedirect('/admin/roles');
        $this->assertEquals($originalCount, $role->fresh()->permissions()->count());
    }

    public function test_non_super_admin_cannot_edit_system_role(): void
    {
        $this->seed();
        $editor = User::factory()->create(['status' => UserStatus::Active]);
        $editorRole = Role::where('name', 'editor')->first();
        $editor->syncRoles([$editorRole->id]);

        $systemRole = Role::where('name', 'super_administrator')->first();

        $response = $this->actingAs($editor)->put("/admin/roles/{$systemRole->id}", [
            'display_name' => 'Changed Name',
            'description' => 'Changed',
            'permissions' => [],
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_be_activated(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->inactive()->create();

        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/activate");

        $response->assertRedirect();
        $this->assertEquals(UserStatus::Active, $user->fresh()->status);
    }
}

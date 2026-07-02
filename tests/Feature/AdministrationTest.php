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

    public function test_dashboard_loads_for_admin(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_users_index_requires_permission(): void
    {
        $this->seed();
        $user = User::factory()->create(['status' => UserStatus::Active]);
        $authorRole = Role::where('name', 'author')->first();
        $user->syncRoles([$authorRole->id]);

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
        $response->assertSee('Photo');
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

    public function test_admin_can_update_user_profile_fields(): void
    {
        $admin = $this->createAdminUser();
        $target = User::factory()->create(['status' => UserStatus::Active]);
        $province = \App\Modules\Address\Models\Province::create(['name' => 'Test Province', 'code' => 'TST', 'is_active' => true]);
        $city = \App\Modules\Address\Models\City::create(['province_id' => $province->id, 'name' => 'Test City', 'is_active' => true]);
        $editorRole = Role::where('name', 'editor')->first();

        $response = $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => $target->name,
            'email' => $target->email,
            'status' => UserStatus::Active->value,
            'roles' => [$editorRole->id],
            'contact_number' => '09170000000',
            'province_id' => $province->id,
            'city_id' => $city->id,
            'about_me' => 'Admin updated bio',
            'remove_avatar' => 0,
        ]);

        $response->assertRedirect('/admin/users');
        $target->refresh();
        $this->assertSame('09170000000', $target->contact_number);
        $this->assertSame($province->id, $target->province_id);
        $this->assertSame($city->id, $target->city_id);
        $this->assertSame('Admin updated bio', $target->about_me);
    }

    public function test_admin_user_edit_shows_profile_fields(): void
    {
        $admin = $this->createAdminUser();
        $target = User::factory()->create(['status' => UserStatus::Active]);

        $response = $this->actingAs($admin)->get("/admin/users/{$target->id}/edit");

        $response->assertOk();
        $response->assertSee('Contact Number');
        $response->assertSee('About Me');
        $response->assertSee('Profile Photo');
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

    public function test_admin_can_bulk_update_user_status(): void
    {
        $admin = $this->createAdminUser();
        $users = User::factory()->inactive()->count(2)->create();

        $response = $this->actingAs($admin)->post('/admin/users/bulk', [
            'bulk_action' => 'update_status',
            'bulk_status' => UserStatus::Active->value,
            'selected' => $users->pluck('id')->all(),
        ]);

        $response->assertRedirect();
        foreach ($users as $user) {
            $this->assertEquals(UserStatus::Active, $user->fresh()->status);
        }
    }

    public function test_admin_can_bulk_verify_user_email(): void
    {
        $admin = $this->createAdminUser();
        $users = User::factory()->count(2)->create([
            'status' => UserStatus::PendingVerification,
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($admin)->post('/admin/users/bulk', [
            'bulk_action' => 'verify_email',
            'selected' => $users->pluck('id')->all(),
        ]);

        $response->assertRedirect();
        foreach ($users as $user) {
            $this->assertNotNull($user->fresh()->email_verified_at);
        }
    }

    public function test_admin_can_bulk_unverify_user_email(): void
    {
        $admin = $this->createAdminUser();
        $users = User::factory()->count(2)->create([
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post('/admin/users/bulk', [
            'bulk_action' => 'unverify_email',
            'selected' => $users->pluck('id')->all(),
        ]);

        $response->assertRedirect();
        foreach ($users as $user) {
            $this->assertNull($user->fresh()->email_verified_at);
        }
    }
}

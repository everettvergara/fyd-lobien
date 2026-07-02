<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Sign In');
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $this->seed();
        $user = User::factory()->create(['status' => UserStatus::Active]);
        $viewerRole = Role::where('name', 'viewer')->first();
        $user->syncRoles([$viewerRole->id]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('login_histories', [
            'email' => $user->email,
            'success' => true,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'module' => 'authentication',
            'action' => 'login',
            'user_id' => $user->id,
        ]);
    }

    public function test_failed_login_is_recorded_in_login_history(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Active,
        ]);

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $this->assertDatabaseHas('login_histories', [
            'email' => $user->email,
            'success' => false,
            'failure_reason' => 'invalid_credentials',
        ]);
    }

    public function test_logout_is_recorded_in_activity_log(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Active,
        ]);

        $this->actingAs($user)->post('/admin/logout');

        $this->assertDatabaseHas('activity_log', [
            'module' => 'authentication',
            'action' => 'logout',
            'user_id' => $user->id,
        ]);
    }
    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Active,
        ]);

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_unverified_users_cannot_login(): void
    {
        $user = User::factory()->unverified()->create();

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_users_cannot_login(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_register(): void
    {
        $response = $this->post('/admin/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/admin/login');
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'status' => UserStatus::PendingVerification->value,
        ]);
    }

    public function test_authenticated_users_can_logout(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->post('/admin/logout');

        $this->assertGuest();
        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_users_without_role_are_sent_to_access_pending(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'status' => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect(route('admin.access.pending'));
        $response = $this->actingAs($user)->get('/admin/access-pending');
        $response->assertOk()->assertSee('Access Pending')->assertSee('Sign Out');
    }

    public function test_authenticated_users_can_logout_via_get(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->get('/admin/logout');

        $this->assertGuest();
        $response->assertRedirect('/admin/login');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_users_can_access_dashboard(): void
    {
        $this->seed();
        $user = User::factory()->create(['status' => UserStatus::Active]);
        $viewerRole = Role::where('name', 'viewer')->first();
        $user->syncRoles([$viewerRole->id]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_unverified_authenticated_users_are_redirected_to_verification_notice(): void
    {
        $user = User::factory()->unverified()->create([
            'status' => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect(route('admin.verification.notice'));
    }
}

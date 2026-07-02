<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\DatabaseSession;
use App\Models\Role;
use App\Models\User;
use App\Services\AuthConfigService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        $this->seed();

        return User::where('email', 'admin@fyd.local')->first();
    }

    public function test_admin_can_view_audit_logs(): void
    {
        $admin = $this->createAdminUser();

        ActivityLog::create([
            'user_id' => $admin->id,
            'module' => 'content',
            'action' => 'created',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/audit-logs');

        $response->assertStatus(200);
        $response->assertSee('Audit Logs');
        $response->assertSee('content');
    }

    public function test_audit_logs_require_permission(): void
    {
        $this->seed();
        $user = User::factory()->create();
        $authorRole = Role::where('name', 'author')->first();
        $user->syncRoles([$authorRole->id]);

        $response = $this->actingAs($user)->get('/admin/audit-logs');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_and_revoke_sessions(): void
    {
        $admin = $this->createAdminUser();

        DatabaseSession::create([
            'id' => 'test-session-id',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => base64_encode('test'),
            'last_activity' => now()->timestamp,
        ]);

        $response = $this->actingAs($admin)->get('/admin/sessions');

        $response->assertStatus(200);
        $response->assertSee('Active Sessions');
        $response->assertSee('127.0.0.1');

        $response = $this->actingAs($admin)->delete('/admin/sessions/test-session-id');

        $response->assertRedirect();
        $this->assertDatabaseMissing('sessions', ['id' => 'test-session-id']);
    }

    public function test_registration_can_be_disabled_via_settings(): void
    {
        $this->seed();

        app(SettingsService::class)->set('auth', 'registration_enabled', 'false', 'boolean');

        $this->assertFalse(app(AuthConfigService::class)->registrationEnabled());

        $response = $this->get('/admin/register');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_password_policy_uses_settings(): void
    {
        $this->seed();

        app(SettingsService::class)->set('auth', 'password_min_length', '12', 'string');
        app(SettingsService::class)->set('auth', 'password_symbols', 'true', 'boolean');

        app(\App\Services\PasswordPolicyService::class)->apply();

        $validator = validator(['password' => 'short'], ['password' => ['required', Password::defaults()]]);
        $this->assertTrue($validator->fails());

        $validator = validator(['password' => 'LongPassword1!'], ['password' => ['required', Password::defaults()]]);
        $this->assertFalse($validator->fails());
    }

    public function test_settings_uses_policy_authorization(): void
    {
        $this->seed();
        $user = User::factory()->create();
        $viewerRole = Role::where('name', 'viewer')->first();
        $user->syncRoles([$viewerRole->id]);

        $response = $this->actingAs($user)->put('/admin/settings', [
            'settings' => [
                'general' => ['website_name' => 'Hacked'],
            ],
        ]);

        $response->assertStatus(403);
    }
}

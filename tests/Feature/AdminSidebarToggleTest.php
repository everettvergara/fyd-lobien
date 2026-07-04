<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSidebarToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        $this->seed();

        return User::where('email', 'admin@fyd.local')->first();
    }

    public function test_admin_layout_includes_sidebar_toggle_control(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('data-admin-sidebar-toggle', false);
        $response->assertSee('data-sidebar-icon-visible', false);
        $response->assertSee('data-sidebar-icon-hidden', false);
        $response->assertSee('bi-layout-sidebar-inset', false);
        $response->assertSee('Hide menu', false);
    }

    public function test_admin_layout_includes_sidebar_hidden_fouc_guard(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee("localStorage.getItem('admin-sidebar-panel-hidden')", false);
        $response->assertSee('admin-sidebar-hidden', false);
    }
}

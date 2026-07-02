<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ModuleStandardizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_module_uses_database_migrations_path(): void
    {
        $this->assertDirectoryExists(app_path('Modules/Settings/Database/Migrations'));
        $this->assertTrue(Schema::hasTable('settings'));
    }

    public function test_content_index_renders_admin_components(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $response = $this->actingAs($admin)->get('/admin/content');

        $response->assertOk();
        $response->assertSee('Content');
        $response->assertSee('Add Content');
    }
}

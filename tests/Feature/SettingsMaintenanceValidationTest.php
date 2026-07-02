<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\Content\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsMaintenanceValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@fyd.local')->firstOrFail();
    }

    public function test_settings_save_accepts_valid_maintenance_page_url(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'settings' => [
                'general' => [
                    'maintenance_mode' => '1',
                    'maintenance_page_url' => '/site-maintenance',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_settings_save_rejects_unknown_maintenance_page_url(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'settings' => [
                'general' => [
                    'maintenance_mode' => '1',
                    'maintenance_page_url' => '/missing-page',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('settings.general.maintenance_page_url');
    }

    public function test_settings_save_rejects_draft_maintenance_page_url(): void
    {
        Content::updateOrCreate(
            ['slug' => 'draft-maintenance'],
            [
                'content_type' => 'page',
                'title' => 'Draft Maintenance',
                'summary' => 'Draft',
                'body' => '<p>Draft</p>',
                'status' => ContentStatus::Draft,
                'author_id' => $this->admin->id,
            ]
        );

        $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'settings' => [
                'general' => [
                    'maintenance_mode' => '1',
                    'maintenance_page_url' => '/draft-maintenance',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('settings.general.maintenance_page_url');
    }

    public function test_settings_save_requires_maintenance_page_url_when_maintenance_mode_enabled(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'settings' => [
                'general' => [
                    'maintenance_mode' => '1',
                    'maintenance_page_url' => '',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('settings.general.maintenance_page_url');
    }

    public function test_settings_save_rejects_reserved_maintenance_page_url(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'settings' => [
                'general' => [
                    'maintenance_mode' => '0',
                    'maintenance_page_url' => '/admin',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('settings.general.maintenance_page_url');
    }
}

<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Setting;
use App\Modules\Content\Models\Content;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_homepage_is_accessible_when_maintenance_mode_is_off(): void
    {
        $this->setMaintenanceMode(false);

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_homepage_redirects_to_maintenance_page_when_maintenance_mode_is_on(): void
    {
        $this->setMaintenanceMode(true);

        $response = $this->get('/');

        $response->assertRedirect('/site-maintenance');
    }

    public function test_maintenance_page_is_accessible_during_maintenance_mode(): void
    {
        $this->setMaintenanceMode(true);

        $response = $this->get('/site-maintenance');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Page/Show')
            ->where('page.slug', 'site-maintenance')
        );
    }

    public function test_admin_login_is_accessible_during_maintenance_mode(): void
    {
        $this->setMaintenanceMode(true);

        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_seeded_maintenance_page_exists_and_is_published(): void
    {
        $content = Content::where('slug', 'site-maintenance')->first();

        $this->assertNotNull($content);
        $this->assertSame('page', $content->content_type);
        $this->assertSame(ContentStatus::Published, $content->status);
        $this->assertNotNull($content->published_at);
    }

    public function test_maintenance_settings_are_seeded(): void
    {
        $this->assertSame('false', Setting::where('group', 'general')->where('key', 'maintenance_mode')->value('value'));
        $this->assertSame('/site-maintenance', Setting::where('group', 'general')->where('key', 'maintenance_page_url')->value('value'));
    }

    protected function setMaintenanceMode(bool $enabled): void
    {
        app(SettingsService::class)->set(
            'general',
            'maintenance_mode',
            $enabled ? 'true' : 'false',
            'boolean'
        );
    }
}

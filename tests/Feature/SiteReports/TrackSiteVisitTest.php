<?php

namespace Tests\Feature\SiteReports;

use App\Models\User;
use App\Modules\SiteReports\Models\BlockedIp;
use App\Modules\SiteReports\Models\SiteVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackSiteVisitTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@fyd.local')->first();
    }

    public function test_public_get_request_creates_site_visit(): void
    {
        $this->get('/');

        $this->assertDatabaseCount('site_visits', 1);
        $this->assertDatabaseHas('site_visits', [
            'path' => '/',
            'route_name' => 'home',
        ]);
    }

    public function test_admin_panel_request_does_not_create_site_visit(): void
    {
        $this->actingAs($this->admin)->get('/admin');

        $this->assertDatabaseCount('site_visits', 0);
    }

    public function test_logged_in_user_public_page_view_is_tracked(): void
    {
        $this->actingAs($this->admin)->get('/');

        $this->assertDatabaseCount('site_visits', 1);
        $this->assertDatabaseHas('site_visits', [
            'path' => '/',
            'route_name' => 'home',
        ]);
    }

    public function test_bot_user_agent_is_not_tracked(): void
    {
        $this->withHeaders([
            'User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
        ])->get('/');

        $this->assertDatabaseCount('site_visits', 0);
    }

    public function test_self_referral_is_stored_without_referer_host(): void
    {
        $this->withHeaders([
            'Referer' => url('/'),
        ])->get('/');

        $visit = SiteVisit::first();

        $this->assertNotNull($visit);
        $this->assertNull($visit->referer_host);
    }

    public function test_external_referer_host_is_recorded(): void
    {
        $this->withHeaders([
            'Referer' => 'https://example.com/page',
        ])->get('/');

        $this->assertDatabaseHas('site_visits', [
            'referer_host' => 'example.com',
        ]);
    }
}

<?php

namespace Tests\Feature\SiteReports;

use App\Models\User;
use App\Modules\SiteReports\Models\SiteVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteReportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $viewer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@fyd.local')->first();
        $this->viewer = User::where('email', 'viewer@fyd.local')->first();
    }

    public function test_page_report_lists_most_visited_pages(): void
    {
        SiteVisit::create([
            'path' => '/',
            'route_name' => 'home',
            'ip_address' => '127.0.0.1',
            'visited_at' => now(),
        ]);
        SiteVisit::create([
            'path' => '/about',
            'route_name' => 'content.show',
            'ip_address' => '127.0.0.1',
            'visited_at' => now(),
        ]);
        SiteVisit::create([
            'path' => '/about',
            'route_name' => 'content.show',
            'ip_address' => '127.0.0.2',
            'visited_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/site-reports/pages');

        $response->assertOk();
        $response->assertSee('Most Visited Pages', false);
        $response->assertSee('/about', false);
        $response->assertSee('2', false);
    }

    public function test_ip_report_lists_hits_by_ip(): void
    {
        SiteVisit::create([
            'path' => '/',
            'route_name' => 'home',
            'ip_address' => '203.0.113.50',
            'visited_at' => now(),
        ]);
        SiteVisit::create([
            'path' => '/about',
            'route_name' => 'content.show',
            'ip_address' => '203.0.113.50',
            'visited_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/site-reports/ips');

        $response->assertOk();
        $response->assertSee('203.0.113.50', false);
        $response->assertSee('Block IP', false);
    }

    public function test_referrer_report_lists_external_referrers_and_summary(): void
    {
        SiteVisit::create([
            'path' => '/',
            'route_name' => 'home',
            'ip_address' => '127.0.0.1',
            'referer_host' => 'news.example.org',
            'visited_at' => now(),
        ]);
        SiteVisit::create([
            'path' => '/',
            'route_name' => 'home',
            'ip_address' => '127.0.0.2',
            'referer_host' => null,
            'visited_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/site-reports/referrers');

        $response->assertOk();
        $response->assertSee('news.example.org', false);
        $response->assertSee('Total hits', false);
        $response->assertSee('Direct hits', false);
    }

    public function test_viewer_cannot_access_site_reports(): void
    {
        $response = $this->actingAs($this->viewer)->get('/admin/site-reports/pages');

        $response->assertForbidden();
    }
}

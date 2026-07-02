<?php

namespace Tests\Feature\SiteReports;

use App\Models\User;
use App\Modules\SiteReports\Models\BlockedIp;
use App\Modules\SiteReports\Models\SiteVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockedIpTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@fyd.local')->first();
    }

    public function test_blocked_ip_receives_403_on_public_site(): void
    {
        BlockedIp::create([
            'ip_address' => '127.0.0.1',
            'blocked_by' => $this->admin->id,
        ]);

        $response = $this->get('/');

        $response->assertForbidden();
        $this->assertDatabaseCount('site_visits', 0);
    }

    public function test_admin_can_block_ip_from_report(): void
    {
        SiteVisit::create([
            'path' => '/',
            'route_name' => 'home',
            'ip_address' => '203.0.113.10',
            'visited_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.site-reports.blocked-ips.store', [
            'ip' => '203.0.113.10',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('blocked_ips', [
            'ip_address' => '203.0.113.10',
            'blocked_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_unblock_ip(): void
    {
        $blocked = BlockedIp::create([
            'ip_address' => '203.0.113.20',
            'blocked_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.site-reports.blocked-ips.destroy', $blocked));

        $response->assertRedirect();
        $this->assertDatabaseMissing('blocked_ips', [
            'ip_address' => '203.0.113.20',
        ]);
    }
}

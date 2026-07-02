<?php

namespace Tests\Feature\SiteReports;

use App\Modules\SiteReports\Models\SiteVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneSiteVisitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_prune_command_deletes_records_older_than_one_week(): void
    {
        SiteVisit::create([
            'path' => '/old',
            'route_name' => 'home',
            'ip_address' => '127.0.0.1',
            'visited_at' => now()->subDays(8),
        ]);
        SiteVisit::create([
            'path' => '/recent',
            'route_name' => 'home',
            'ip_address' => '127.0.0.1',
            'visited_at' => now()->subDay(),
        ]);

        $this->artisan('site-reports:prune')->assertSuccessful();

        $this->assertDatabaseMissing('site_visits', ['path' => '/old']);
        $this->assertDatabaseHas('site_visits', ['path' => '/recent']);
    }
}

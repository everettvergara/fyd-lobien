<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Models\BannerTemplate;
use App\Modules\Content\Models\Content;
use App\Modules\Menus\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_content_seeder_populates_content(): void
    {
        $this->seed();

        $this->assertGreaterThanOrEqual(9, Content::count());
        $this->assertGreaterThanOrEqual(5, Content::where('content_type', 'page')->count());
        $this->assertGreaterThanOrEqual(4, Content::where('content_type', 'article')->count());
        $this->assertSame(BannerTemplate::count(), Banner::count());
        $this->assertGreaterThanOrEqual(12, Banner::count());
        $this->assertSame(2, Menu::count());
        $this->assertSame(1, User::count());
    }

    public function test_sample_content_is_publicly_accessible(): void
    {
        $this->seed();

        $this->get('/about')->assertStatus(200);
        $this->get('/services')->assertStatus(200);
        $this->get('/contact')->assertStatus(200);
    }

    public function test_admin_can_login(): void
    {
        $this->seed();

        $response = $this->post('/admin/login', [
            'email' => 'admin@fyd.local',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin');
    }
}

<?php

namespace Tests\Feature;

use App\Modules\Banners\Models\Banner;
use App\Modules\Menus\Models\Menu;
use App\Modules\Pages\Models\Page;
use App\Modules\Posts\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_populates_content(): void
    {
        $this->seed();

        $this->assertGreaterThanOrEqual(5, Page::count());
        $this->assertGreaterThanOrEqual(4, Post::count());
        $this->assertGreaterThanOrEqual(3, Banner::count());
        $this->assertGreaterThanOrEqual(2, Menu::count());
        $this->assertGreaterThanOrEqual(4, User::count());
    }

    public function test_demo_pages_are_publicly_accessible(): void
    {
        $this->seed();

        $this->get('/about')->assertStatus(200);
        $this->get('/services')->assertStatus(200);
        $this->get('/contact')->assertStatus(200);
        $this->get('/blog/welcome-to-fyd-corporate')->assertStatus(200);
    }

    public function test_demo_users_can_login(): void
    {
        $this->seed();

        $response = $this->post('/admin/login', [
            'email' => 'editor@fyd.local',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin');
    }
}

<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        $this->seed();

        return User::where('email', 'admin@fyd.local')->first();
    }

    public function test_admin_can_view_pages_list(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/pages');
        $response->assertStatus(200)->assertSee('Pages');
    }

    public function test_admin_can_create_page(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/pages', [
            'title' => 'About Us',
            'slug' => 'about-us',
            'summary' => 'About our company',
            'content' => 'Page content here',
            'status' => ContentStatus::Draft->value,
            'template' => 'default',
        ]);

        $response->assertRedirect('/admin/pages');
        $this->assertDatabaseHas('pages', ['slug' => 'about-us']);
    }

    public function test_admin_can_create_post(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/posts', [
            'title' => 'First Blog Post',
            'slug' => 'first-blog-post',
            'content' => 'Blog content',
            'status' => ContentStatus::Draft->value,
        ]);

        $response->assertRedirect('/admin/posts');
        $this->assertDatabaseHas('posts', ['slug' => 'first-blog-post']);
    }

    public function test_admin_can_create_banner(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/banners', [
            'name' => 'Homepage Hero',
            'title' => 'Welcome',
            'type' => 'hero',
            'placement' => 'homepage_hero',
            'status' => ContentStatus::Draft->value,
        ]);

        $response->assertRedirect('/admin/banners');
        $this->assertDatabaseHas('banners', ['name' => 'Homepage Hero']);
    }

    public function test_admin_can_view_settings(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/settings');
        $response->assertStatus(200)->assertSee('Settings');
    }

    public function test_dashboard_shows_content_counts(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/pages', [
            'title' => 'Test', 'slug' => 'test', 'status' => ContentStatus::Draft->value,
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200)->assertSee('Pages');
    }
}

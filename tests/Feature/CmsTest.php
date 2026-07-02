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

    public function test_admin_can_view_content_list(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/content');
        $response->assertStatus(200)->assertSee('Content');
    }

    public function test_admin_can_create_page_content(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/content', [
            'content_type' => 'page',
            'title' => 'About Us',
            'slug' => 'about-us',
            'summary' => 'About our company',
            'body' => '<p>Page content here</p>',
            'status' => ContentStatus::Draft->value,
        ]);

        $response->assertRedirect('/admin/content');
        $this->assertDatabaseHas('contents', ['slug' => 'about-us', 'content_type' => 'page']);
    }

    public function test_admin_can_create_article_content(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/content', [
            'content_type' => 'article',
            'title' => 'First Article',
            'slug' => 'first-article',
            'body' => '<p>Article content</p>',
            'status' => ContentStatus::Draft->value,
        ]);

        $response->assertRedirect('/admin/content');
        $this->assertDatabaseHas('contents', ['slug' => 'first-article', 'content_type' => 'article']);
    }

    public function test_admin_can_create_banner(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/banners', [
            'name' => 'Homepage Hero',
            'key' => 'cms-test-hero',
            'title' => 'Welcome',
            'type' => 'hero',
            'status' => ContentStatus::Draft->value,
        ]);

        $response->assertRedirect('/admin/banners');
        $this->assertDatabaseHas('banners', ['name' => 'Homepage Hero']);
    }

    public function test_admin_can_view_settings(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/settings');
        $response->assertStatus(200)
            ->assertSee('Settings')
            ->assertSee('Site Logo')
            ->assertSee('Favicon');
    }

    public function test_dashboard_loads_for_admin(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200)->assertSee('Dashboard');
    }
}

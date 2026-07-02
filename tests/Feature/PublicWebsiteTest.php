<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\MenuLocation;
use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Models\MenuItem;
use App\Services\Recaptcha\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->author = User::where('email', 'admin@fyd.local')->first();
    }

    public function test_homepage_renders(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_published_content_is_accessible(): void
    {
        Content::create([
            'content_type' => 'page',
            'title' => 'About Us',
            'slug' => 'about-us',
            'summary' => 'About our company',
            'body' => '<p>We are a great company.</p>',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/about-us');
        $response->assertStatus(200);
    }

    public function test_draft_content_returns_404(): void
    {
        Content::create([
            'content_type' => 'page',
            'title' => 'Draft Content',
            'slug' => 'draft-content',
            'status' => ContentStatus::Draft,
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/draft-content');
        $response->assertStatus(404);
    }

    public function test_published_article_is_accessible_at_slug_url(): void
    {
        Content::create([
            'content_type' => 'article',
            'title' => 'Hello World',
            'slug' => 'hello-world',
            'body' => '<p>First article content</p>',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/hello-world');
        $response->assertStatus(200);
    }

    public function test_blog_route_returns_404(): void
    {
        $response = $this->get('/blog');
        $response->assertStatus(404);
    }

    public function test_search_finds_content(): void
    {
        $this->assertDatabaseHas('contents', ['slug' => 'services']);

        $response = $this->get('/search?q=services');
        $response->assertStatus(200);
    }

    public function test_search_submission_redirects_to_results(): void
    {
        $response = $this->post('/search', [
            'q' => 'services',
        ]);

        $response->assertRedirect('/search?q=services');
    }

    public function test_search_submission_requires_recaptcha_when_enabled(): void
    {
        config([
            'recaptcha.site_key' => 'test-site-key',
            'recaptcha.secret_key' => 'test-secret-key',
            'recaptcha.enabled' => true,
        ]);

        $response = $this->post('/search', [
            'q' => 'services',
        ]);

        $response->assertSessionHasErrors('recaptcha_token');
    }

    public function test_search_submission_accepts_valid_recaptcha_token(): void
    {
        config([
            'recaptcha.site_key' => 'test-site-key',
            'recaptcha.secret_key' => 'test-secret-key',
            'recaptcha.enabled' => true,
        ]);

        $this->mock(RecaptchaService::class, function ($mock): void {
            $mock->shouldReceive('enabled')->andReturn(true);
            $mock->shouldReceive('verify')
                ->once()
                ->with('valid-token', 'search', \Mockery::any())
                ->andReturn(true);
        });

        $response = $this->post('/search', [
            'q' => 'services',
            'recaptcha_token' => 'valid-token',
        ]);

        $response->assertRedirect('/search?q=services');
    }

    public function test_recaptcha_config_is_shared_with_public_pages(): void
    {
        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->has('recaptcha')
            ->where('recaptcha.enabled', false)
        );
    }

    public function test_homepage_includes_published_banner(): void
    {
        $this->assertDatabaseHas('banners', [
            'key' => 'homepage-hero',
            'status' => ContentStatus::Published->value,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_content_page_includes_inner_page_banner(): void
    {
        $response = $this->get('/about');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Content/Show')
            ->has('banner')
            ->where('banner.template.key', 'inner_page')
            ->where('banner.title', 'About Us')
        );
    }

    public function test_navigation_menu_is_shared(): void
    {
        $menu = Menu::create(['name' => 'Main', 'location' => MenuLocation::Header]);
        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Services',
            'url' => '/services',
            'link_type' => 'internal',
            'target' => '_self',
            'sort_order' => 0,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\MenuLocation;
use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
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

    public function test_published_page_is_accessible(): void
    {
        $page = Page::create([
            'path' => '/about-us',
            'slug' => 'about-us',
            'title' => 'About Us',
            'summary' => 'About our company',
            'body' => '<p>We are a great company.</p>',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => 'page-body',
            'sort_order' => 0,
            'config' => [],
        ]);

        $this->get('/about-us')->assertStatus(200);
    }

    public function test_draft_page_returns_404(): void
    {
        Page::create([
            'path' => '/draft-content',
            'slug' => 'draft-content',
            'title' => 'Draft Page',
            'status' => ContentStatus::Draft,
            'author_id' => $this->author->id,
        ]);

        $this->get('/draft-content')->assertStatus(404);
    }

    public function test_unpublished_article_slug_returns_404_without_page(): void
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

        $this->get('/hello-world')->assertStatus(404);
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

    public function test_homepage_renders_page_manager_layout(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Page/Show')
                ->has('page')
            );
    }

    public function test_seeded_about_page_renders(): void
    {
        $this->get('/about')
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Page/Show')
                ->where('page.path', '/about')
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

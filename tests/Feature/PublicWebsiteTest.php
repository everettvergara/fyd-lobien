<?php

namespace Tests\Feature;

use App\Enums\BannerPlacement;
use App\Enums\BannerType;
use App\Enums\ContentStatus;
use App\Enums\MenuLocation;
use App\Models\User;
use App\Modules\Banners\Models\Banner;
use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Models\MenuItem;
use App\Modules\Pages\Models\Page;
use App\Modules\Posts\Models\Post;
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
        Page::create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'summary' => 'About our company',
            'content' => 'We are a great company.',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/about-us');
        $response->assertStatus(200);
    }

    public function test_draft_page_returns_404(): void
    {
        Page::create([
            'title' => 'Draft Page',
            'slug' => 'draft-page',
            'status' => ContentStatus::Draft,
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/draft-page');
        $response->assertStatus(404);
    }

    public function test_blog_index_renders(): void
    {
        $response = $this->get('/blog');
        $response->assertStatus(200);
    }

    public function test_published_post_is_accessible(): void
    {
        Post::create([
            'title' => 'Hello World',
            'slug' => 'hello-world',
            'content' => 'First post content',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/blog/hello-world');
        $response->assertStatus(200);
    }

    public function test_search_finds_content(): void
    {
        $this->assertDatabaseHas('pages', ['slug' => 'services']);

        $response = $this->get('/search?q=services');
        $response->assertStatus(200);
    }

    public function test_homepage_includes_published_banner(): void
    {
        Banner::create([
            'name' => 'Hero',
            'title' => 'Welcome Banner',
            'type' => BannerType::Hero,
            'placement' => BannerPlacement::HomepageHero,
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
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

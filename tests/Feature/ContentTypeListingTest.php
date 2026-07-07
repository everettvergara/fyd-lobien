<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Modules\Content\Models\ContentType;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTypeListingTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->author = User::where('email', 'admin@fyd.local')->first();
    }

    public function test_content_type_listing_renders_at_slug_path(): void
    {
        $this->ensureArticleSlug();
        $this->clearArticles();

        $this->createPublishedArticle('First Article', 'first-article');
        $this->createPublishedArticle('Second Article', 'second-article');

        $this->get('/articles')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->component('Page/Show')
                ->where('page.path', '/articles')
                ->where('page.title', 'Article')
                ->where('regions.main.0.type', 'content-type-listing')
                ->where('regions.main.0.component', 'ContentTypeListingBlock')
                ->has('regions.main.0.props.listing.items', 2)
                ->where('regions.main.0.props.listing.items.0.title', 'Second Article')
                ->where('regions.main.0.props.listing.items.0.path', 'articles/second-article')
                ->where('regions.main.0.props.listing.items.1.title', 'First Article')
                ->where('regions.main.0.props.listing.items.1.path', 'articles/first-article'));
    }

    public function test_content_type_listing_supports_pagination(): void
    {
        $this->ensureArticleSlug();
        $this->clearArticles();

        for ($index = 1; $index <= 11; $index++) {
            $this->createPublishedArticle("Article {$index}", "article-{$index}");
        }

        $this->get('/articles')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->has('regions.main.0.props.listing.items', 10)
                ->where('regions.main.0.props.listing.pagination.currentPage', 1)
                ->where('regions.main.0.props.listing.pagination.lastPage', 2)
                ->where('regions.main.0.props.listing.pagination.queryParam', 'ct_article_page'));

        $this->get('/articles?ct_article_page=2')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->has('regions.main.0.props.listing.items', 1)
                ->where('regions.main.0.props.listing.pagination.currentPage', 2));
    }

    public function test_page_manager_page_wins_over_auto_content_type_listing(): void
    {
        $this->ensureArticleSlug();

        $this->createPublishedArticle('Listed Article', 'listed-article');

        $page = Page::create([
            'path' => '/articles',
            'slug' => 'articles',
            'title' => 'Custom Articles Page',
            'summary' => 'Managed page',
            'body' => '<p>Custom body</p>',
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

        $this->get('/articles')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->component('Page/Show')
                ->where('page.title', 'Custom Articles Page')
                ->missing('regions.main.0.props.listing'));
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/unknown-slug')->assertNotFound();
    }

    public function test_content_type_without_slug_does_not_auto_list(): void
    {
        ContentType::where('key', 'article')->update(['slug' => null]);

        $this->createPublishedArticle('Orphan Article', 'orphan-article');

        $this->get('/articles')->assertNotFound();
    }

    protected function ensureArticleSlug(): void
    {
        ContentType::where('key', 'article')->update(['slug' => 'articles']);
    }

    protected function clearArticles(): void
    {
        Content::where('content_type', 'article')->delete();
    }

    protected function createPublishedArticle(string $title, string $slug): Content
    {
        return Content::create([
            'content_type' => 'article',
            'title' => $title,
            'slug' => $slug,
            'body' => "<p>{$title} body</p>",
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);
    }
}

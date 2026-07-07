<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Modules\Content\Models\ContentType;
use App\Modules\Content\Services\ContentPageSyncService;
use App\Modules\Content\Services\ContentUrlService;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentPageSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->author = User::where('email', 'admin@fyd.local')->first();
    }

    public function test_path_for_returns_content_slug_when_type_slug_is_blank(): void
    {
        $content = Content::make([
            'content_type' => 'page',
            'slug' => 'about',
        ]);

        $this->assertSame('about', app(ContentUrlService::class)->pathFor($content));
    }

    public function test_path_for_returns_type_and_content_slug_when_type_slug_is_set(): void
    {
        $this->ensureArticleSlug();

        $content = Content::make([
            'content_type' => 'article',
            'slug' => 'state-of-the-nation',
        ]);

        $this->assertSame('articles/state-of-the-nation', app(ContentUrlService::class)->pathFor($content));
    }

    public function test_publishing_article_creates_page_manager_page(): void
    {
        $this->ensureArticleSlug();

        $content = $this->createPublishedArticle('State of the Nation', 'state-of-the-nation');

        app(ContentPageSyncService::class)->syncContentPage($content);

        $this->assertDatabaseHas('pages', [
            'path' => '/articles/state-of-the-nation',
            'title' => 'State of the Nation',
        ]);

        $page = Page::query()->where('path', '/articles/state-of-the-nation')->first();
        $this->assertNotNull($page);
        $this->assertSame('/articles/state-of-the-nation', $content->fresh()->public_page_path);

        $blockTypes = $page->blocks()->pluck('block_type')->all();
        $this->assertContains('page-header', $blockTypes);
        $this->assertContains('page-body', $blockTypes);
    }

    public function test_article_detail_page_is_publicly_accessible(): void
    {
        $this->ensureArticleSlug();

        $content = $this->createPublishedArticle('Public Article', 'public-article');
        app(ContentPageSyncService::class)->syncContentPage($content);

        $this->get('/articles/public-article')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->component('Page/Show')
                ->where('page.path', '/articles/public-article')
                ->where('page.title', 'Public Article'));
    }

    public function test_page_type_syncs_to_root_content_slug_path(): void
    {
        $content = Content::create([
            'content_type' => 'page',
            'title' => 'About Us',
            'slug' => 'about-us',
            'body' => '<p>About body</p>',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);

        app(ContentPageSyncService::class)->syncContentPage($content);

        $this->assertDatabaseHas('pages', ['path' => '/about-us']);
        $this->get('/about-us')->assertOk();
    }

    public function test_content_slug_change_moves_page_path_in_place(): void
    {
        $this->ensureArticleSlug();

        $content = $this->createPublishedArticle('Original Title', 'original-slug');
        app(ContentPageSyncService::class)->syncContentPage($content);

        $page = Page::query()->where('path', '/articles/original-slug')->first();
        $this->assertNotNull($page);
        $page->blocks()->create([
            'region_key' => 'main',
            'block_type' => 'page-body',
            'sort_order' => 99,
            'config' => [],
        ]);
        $blockCount = $page->blocks()->count();

        $content->update(['slug' => 'updated-slug']);
        app(ContentPageSyncService::class)->syncContentPage($content->fresh());

        $page->refresh();
        $this->assertSame('/articles/updated-slug', $page->path);
        $this->assertSame('/articles/updated-slug', $content->fresh()->public_page_path);
        $this->assertSame($blockCount, $page->blocks()->count());
        $this->get('/articles/original-slug')->assertNotFound();
        $this->get('/articles/updated-slug')->assertOk();
    }

    public function test_content_type_slug_change_moves_all_published_entries(): void
    {
        $this->ensureArticleSlug();

        $first = $this->createPublishedArticle('First', 'first-entry');
        $second = $this->createPublishedArticle('Second', 'second-entry');
        app(ContentPageSyncService::class)->syncContentPage($first);
        app(ContentPageSyncService::class)->syncContentPage($second);

        $type = ContentType::where('key', 'article')->firstOrFail();
        $type->update(['slug' => 'news']);
        app(\App\Support\ContentTypeRegistry::class)->forgetCache();
        app(ContentPageSyncService::class)->syncAllForType($type->fresh());

        $this->assertDatabaseHas('pages', ['path' => '/news/first-entry']);
        $this->assertDatabaseHas('pages', ['path' => '/news/second-entry']);
        $this->assertDatabaseMissing('pages', ['path' => '/articles/first-entry']);
        $this->get('/news/first-entry')->assertOk();
        $this->get('/news/second-entry')->assertOk();
    }

    public function test_content_type_slug_cleared_moves_to_root_content_slug(): void
    {
        $this->ensureArticleSlug();

        $content = $this->createPublishedArticle('Standalone', 'standalone-entry');
        app(ContentPageSyncService::class)->syncContentPage($content);

        ContentType::where('key', 'article')->update(['slug' => null]);
        app(\App\Support\ContentTypeRegistry::class)->forgetCache();

        app(ContentPageSyncService::class)->syncContentPage($content->fresh());

        $this->assertDatabaseHas('pages', ['path' => '/standalone-entry']);
        $this->assertDatabaseMissing('pages', ['path' => '/articles/standalone-entry']);
        $this->get('/standalone-entry')->assertOk();
    }

    public function test_content_reassigned_to_another_type_recomputes_path(): void
    {
        ContentType::create([
            'key' => 'news',
            'slug' => 'news',
            'label' => 'News',
            'icon' => 'bi-newspaper',
            'sort_order' => 99,
            'is_active' => true,
        ]);
        app(\App\Support\ContentTypeRegistry::class)->forgetCache();

        $this->ensureArticleSlug();

        $content = $this->createPublishedArticle('Reassigned', 'reassigned-entry');
        app(ContentPageSyncService::class)->syncContentPage($content);

        $content->update(['content_type' => 'news']);
        app(ContentPageSyncService::class)->syncContentPage($content->fresh());

        $this->assertDatabaseHas('pages', ['path' => '/news/reassigned-entry']);
        $this->assertDatabaseMissing('pages', ['path' => '/articles/reassigned-entry']);
        $this->get('/news/reassigned-entry')->assertOk();
    }

    public function test_archiving_content_removes_synced_page(): void
    {
        $this->ensureArticleSlug();

        $content = $this->createPublishedArticle('Archive Me', 'archive-me');
        app(ContentPageSyncService::class)->syncContentPage($content);

        $content->update(['status' => ContentStatus::Archived]);
        app(ContentPageSyncService::class)->syncContentPage($content->fresh());

        $this->assertDatabaseMissing('pages', ['path' => '/articles/archive-me']);
        $this->assertNull($content->fresh()->public_page_path);
        $this->get('/articles/archive-me')->assertNotFound();
    }

    public function test_deleting_content_removes_synced_page(): void
    {
        $this->ensureArticleSlug();

        $content = $this->createPublishedArticle('Delete Me', 'delete-me');
        app(ContentPageSyncService::class)->syncContentPage($content);

        app(ContentPageSyncService::class)->removeContentPage($content);
        $content->delete();

        $this->assertDatabaseMissing('pages', ['path' => '/articles/delete-me']);
    }

    public function test_updating_title_and_body_keeps_same_path(): void
    {
        $this->ensureArticleSlug();

        $content = $this->createPublishedArticle('Original Title', 'same-path');
        app(ContentPageSyncService::class)->syncContentPage($content);

        $content->update([
            'title' => 'Updated Title',
            'body' => '<p>Updated body</p>',
        ]);
        app(ContentPageSyncService::class)->syncContentPage($content->fresh());

        $page = Page::query()->where('path', '/articles/same-path')->first();
        $this->assertNotNull($page);
        $this->assertSame('Updated Title', $page->title);
        $this->assertSame('<p>Updated body</p>', $page->body);
    }

    public function test_synced_page_can_be_customized_in_page_manager(): void
    {
        $this->ensureArticleSlug();

        $content = $this->createPublishedArticle('Listed Article', 'listed-article');
        app(ContentPageSyncService::class)->syncContentPage($content);

        $page = Page::query()->where('path', '/articles/listed-article')->firstOrFail();
        $page->update(['title' => 'Custom Override Page', 'body' => '<p>Custom body</p>']);
        $page->blocks()->delete();
        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => 'page-body',
            'sort_order' => 0,
            'config' => [],
        ]);

        $this->get('/articles/listed-article')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->where('page.title', 'Custom Override Page'));
    }

    protected function ensureArticleSlug(): void
    {
        ContentType::where('key', 'article')->update(['slug' => 'articles']);
        app(\App\Support\ContentTypeRegistry::class)->forgetCache();
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

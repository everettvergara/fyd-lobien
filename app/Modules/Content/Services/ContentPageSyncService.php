<?php

namespace App\Modules\Content\Services;

use App\Enums\ContentStatus;
use App\Modules\Content\Models\Content;
use App\Modules\Content\Models\ContentType;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Support\ContentTypeRegistry;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class ContentPageSyncService
{
    public function __construct(
        protected ContentUrlService $urls,
        protected ContentTypeRegistry $contentTypes,
    ) {}

    public function pathFor(Content $content): ?string
    {
        $relative = $this->urls->pathFor($content);

        if ($relative === null) {
            return null;
        }

        return '/'.$relative;
    }

    public function isEligible(Content $content): bool
    {
        if (trim((string) $content->slug) === '') {
            return false;
        }

        return $content->status === ContentStatus::Published
            && ($content->published_at === null || $content->published_at->lte(now()));
    }

    /**
     * @return array{created: int, updated: int, moved: int, removed: int, error: ?string}
     */
    public function syncContentPage(Content $content): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'moved' => 0,
            'removed' => 0,
            'error' => null,
        ];

        if (! $this->isEligible($content)) {
            if ($this->removeContentPage($content)) {
                $stats['removed'] = 1;
            }

            return $stats;
        }

        $path = $this->pathFor($content);

        if ($path === null) {
            return $stats;
        }

        try {
            $content->loadMissing(['seoMeta', 'featuredImage']);

            $oldPath = $content->public_page_path;
            $page = null;

            if (is_string($oldPath) && $oldPath !== '' && $oldPath !== $path) {
                $page = $this->movePagePath($content, $oldPath, $path);
                $stats['moved'] = 1;
            } else {
                Page::purgeSoftDeletedAtPath($path);
                $existing = Page::liveOccupantAtPath($path);
                $created = $existing === null;

                $page = Page::updateOrCreate(
                    ['path' => $path],
                    $this->pageAttributes($content, $path),
                );

                if ($created) {
                    $stats['created'] = 1;
                } else {
                    $stats['updated'] = 1;
                }
            }

            $this->syncPageFields($page, $content);
            $this->syncPageSeo($page, $content);
            $this->ensureDefaultBlocks($page, $content);
            $this->updatePublicPagePath($content, $path);
        } catch (ContentPageSyncException $exception) {
            $stats['error'] = $exception->getMessage();
        } catch (UniqueConstraintViolationException $exception) {
            $stats['error'] = "Cannot sync page to {$path}: path is already in use.";
        }

        return $stats;
    }

    /**
     * @return array{created: int, updated: int, moved: int, removed: int, errors: array<int, string>}
     */
    public function syncAllForType(ContentType $type): array
    {
        $this->contentTypes->forgetCache();

        $stats = [
            'created' => 0,
            'updated' => 0,
            'moved' => 0,
            'removed' => 0,
            'errors' => [],
        ];

        Content::query()
            ->published()
            ->where('content_type', $type->key)
            ->each(function (Content $content) use (&$stats) {
                $result = $this->syncContentPage($content->fresh());
                $this->mergeStats($stats, $result);
            });

        return $stats;
    }

    /**
     * @return array{created: int, updated: int, moved: int, removed: int, errors: array<int, string>}
     */
    public function syncAll(): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'moved' => 0,
            'removed' => 0,
            'errors' => [],
        ];

        Content::query()
            ->published()
            ->each(function (Content $content) use (&$stats) {
                $result = $this->syncContentPage($content->fresh());
                $this->mergeStats($stats, $result);
            });

        return $stats;
    }

    public function contentForPage(Page $page): ?Content
    {
        return Content::query()
            ->published()
            ->where('public_page_path', $page->path)
            ->with(['featuredImage', 'attachment', 'galleryImages', 'seoMeta', 'author'])
            ->first();
    }

    public function removeContentPage(Content $content): bool
    {
        $path = $content->public_page_path;

        if (! is_string($path) || $path === '') {
            return false;
        }

        $page = Page::query()->where('path', $path)->first();

        if ($page !== null) {
            DB::transaction(function () use ($page) {
                $page->blocks()->delete();
                $page->forceDelete();
            });
        }

        $this->updatePublicPagePath($content, null);

        return true;
    }

    public function movePagePath(Content $content, string $oldPath, string $newPath): Page
    {
        $oldPath = Page::normalizePath($oldPath);
        $newPath = Page::normalizePath($newPath);

        $page = Page::query()->where('path', $oldPath)->first();

        if ($page === null) {
            $this->assertPathAvailable($newPath);

            return Page::updateOrCreate(
                ['path' => $newPath],
                $this->pageAttributes($content, $newPath),
            );
        }

        $this->assertPathAvailable($newPath, $page->id);

        $page->update([
            'path' => $newPath,
            'slug' => Page::slugFromPath($newPath),
        ]);

        return $page->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    protected function pageAttributes(Content $content, string $path): array
    {
        return [
            'slug' => Page::slugFromPath($path),
            'title' => $content->title,
            'summary' => $content->summary,
            'body' => $content->body,
            'status' => $content->status,
            'published_at' => $content->published_at,
            'author_id' => $content->author_id,
            'featured_image_id' => $content->featured_image_id,
        ];
    }

    protected function syncPageFields(Page $page, Content $content): void
    {
        $page->update([
            'title' => $content->title,
            'summary' => $content->summary,
            'body' => $content->body,
            'status' => $content->status,
            'published_at' => $content->published_at,
            'author_id' => $content->author_id,
            'featured_image_id' => $content->featured_image_id,
        ]);
    }

    protected function syncPageSeo(Page $page, Content $content): void
    {
        if ($content->seoMeta === null) {
            return;
        }

        $page->saveSeo($content->seoMeta->only([
            'seo_title', 'meta_description', 'meta_keywords', 'canonical_url',
            'robots', 'og_title', 'og_description', 'og_image_id',
            'sitemap_include', 'sitemap_changefreq', 'sitemap_priority',
        ]));
    }

    protected function ensureDefaultBlocks(Page $page, Content $content): void
    {
        if ($page->blocks()->count() > 0) {
            return;
        }

        $sortOrder = 0;

        if ($content->content_type === 'page') {
            PageBlock::create([
                'page_id' => $page->id,
                'region_key' => 'hero',
                'block_type' => 'banner',
                'sort_order' => $sortOrder++,
                'config' => ['banner_key' => 'page-'.$content->slug],
            ]);
        }

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => 'page-header',
            'sort_order' => $sortOrder++,
            'config' => [],
        ]);

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => 'page-body',
            'sort_order' => $sortOrder++,
            'config' => [],
        ]);

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => 'content-extras',
            'sort_order' => $sortOrder,
            'config' => [],
        ]);
    }

    protected function updatePublicPagePath(Content $content, ?string $path): void
    {
        if ($content->public_page_path === $path) {
            return;
        }

        Content::query()
            ->whereKey($content->id)
            ->update(['public_page_path' => $path]);

        $content->public_page_path = $path;
    }

    protected function assertPathAvailable(string $path, ?int $exceptPageId = null): void
    {
        Page::purgeSoftDeletedAtPath($path);

        $occupant = Page::liveOccupantAtPath($path, $exceptPageId);

        if ($occupant === null) {
            return;
        }

        if ($occupant->is_system) {
            throw new ContentPageSyncException("Cannot move page to {$path}: path is reserved by a system page.");
        }

        throw new ContentPageSyncException("Cannot move page to {$path}: path is already in use.");
    }

    /**
     * @param  array{created: int, updated: int, moved: int, removed: int, errors: array<int, string>}  $stats
     * @param  array{created: int, updated: int, moved: int, removed: int, error: ?string}  $result
     */
    protected function mergeStats(array &$stats, array $result): void
    {
        $stats['created'] += $result['created'];
        $stats['updated'] += $result['updated'];
        $stats['moved'] += $result['moved'];
        $stats['removed'] += $result['removed'];

        if ($result['error'] !== null) {
            $stats['errors'][] = $result['error'];
        }
    }
}

<?php

namespace App\Modules\WebForms\Services;

use App\Enums\ContentStatus;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Modules\WebForms\Models\Webform;
use Illuminate\Support\Facades\DB;

class WebformPageSyncService
{
    public function pathFor(Webform $webform): ?string
    {
        $slug = trim((string) $webform->slug);

        if ($slug === '') {
            return null;
        }

        return Page::normalizePath('/'.$slug);
    }

    public function isEligible(Webform $webform): bool
    {
        if (trim((string) $webform->slug) === '') {
            return false;
        }

        return $webform->is_active;
    }

    /**
     * @return array{created: int, updated: int, moved: int, removed: int, error: ?string}
     */
    public function syncWebformPage(Webform $webform): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'moved' => 0,
            'removed' => 0,
            'error' => null,
        ];

        if (! $this->isEligible($webform)) {
            if ($this->removeWebformPage($webform)) {
                $stats['removed'] = 1;
            }

            return $stats;
        }

        $path = $this->pathFor($webform);

        if ($path === null) {
            return $stats;
        }

        try {
            $oldPath = $webform->public_page_path;
            $page = null;

            if (is_string($oldPath) && $oldPath !== '' && $oldPath !== $path) {
                $page = $this->movePagePath($webform, $oldPath, $path);
                $stats['moved'] = 1;
            } else {
                $existing = Page::query()->where('path', $path)->first();
                $created = $existing === null;

                $page = Page::updateOrCreate(
                    ['path' => $path],
                    $this->pageAttributes($webform, $path),
                );

                if ($created) {
                    $stats['created'] = 1;
                } else {
                    $stats['updated'] = 1;
                }
            }

            $this->syncPageFields($page, $webform);
            $this->ensureDefaultBlocks($page, $webform);
            $this->updatePublicPagePath($webform, $path);
        } catch (WebformPageSyncException $exception) {
            $stats['error'] = $exception->getMessage();
        }

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

        Webform::query()->each(function (Webform $webform) use (&$stats) {
            $result = $this->syncWebformPage($webform->fresh());

            $stats['created'] += $result['created'];
            $stats['updated'] += $result['updated'];
            $stats['moved'] += $result['moved'];
            $stats['removed'] += $result['removed'];

            if ($result['error'] !== null) {
                $stats['errors'][] = $result['error'];
            }
        });

        return $stats;
    }

    public function removeWebformPage(Webform $webform): bool
    {
        $path = $webform->public_page_path;

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

        $this->updatePublicPagePath($webform, null);

        return true;
    }

    public function movePagePath(Webform $webform, string $oldPath, string $newPath): Page
    {
        $oldPath = Page::normalizePath($oldPath);
        $newPath = Page::normalizePath($newPath);

        $page = Page::query()->where('path', $oldPath)->first();

        if ($page === null) {
            return Page::updateOrCreate(
                ['path' => $newPath],
                $this->pageAttributes($webform, $newPath),
            );
        }

        $occupant = Page::query()->where('path', $newPath)->first();

        if ($occupant !== null && $occupant->id !== $page->id) {
            throw new WebformPageSyncException("Cannot move page to {$newPath}: path is already in use.");
        }

        $page->update([
            'path' => $newPath,
            'slug' => Page::slugFromPath($newPath),
        ]);

        return $page->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    protected function pageAttributes(Webform $webform, string $path): array
    {
        return [
            'slug' => Page::slugFromPath($path),
            'title' => $webform->name,
            'summary' => $webform->description,
            'body' => null,
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ];
    }

    protected function syncPageFields(Page $page, Webform $webform): void
    {
        $page->update([
            'title' => $webform->name,
            'summary' => $webform->description,
            'status' => ContentStatus::Published,
            'published_at' => $page->published_at ?? now(),
        ]);

        $this->syncWebformBlockSlug($page, $webform);
    }

    protected function ensureDefaultBlocks(Page $page, Webform $webform): void
    {
        if ($page->blocks()->count() > 0) {
            return;
        }

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => 'webform',
            'sort_order' => 0,
            'config' => ['webform_slug' => $webform->slug],
        ]);
    }

    protected function syncWebformBlockSlug(Page $page, Webform $webform): void
    {
        $page->blocks()
            ->where('block_type', 'webform')
            ->get()
            ->each(function (PageBlock $block) use ($webform) {
                $config = $block->config ?? [];

                if (($config['webform_slug'] ?? '') === $webform->slug) {
                    return;
                }

                $block->update([
                    'config' => array_merge($config, ['webform_slug' => $webform->slug]),
                ]);
            });
    }

    protected function updatePublicPagePath(Webform $webform, ?string $path): void
    {
        if ($webform->public_page_path === $path) {
            return;
        }

        Webform::query()
            ->whereKey($webform->id)
            ->update(['public_page_path' => $path]);

        $webform->public_page_path = $path;
    }
}

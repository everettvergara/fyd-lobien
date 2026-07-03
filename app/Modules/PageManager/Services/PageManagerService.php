<?php

namespace App\Modules\PageManager\Services;

use App\Enums\ContentStatus;
use App\Modules\PageManager\Models\Page;

class PageManagerService
{
    public function resolvePublishedPage(string $path): ?Page
    {
        $normalized = Page::normalizePath($path);

        $page = $this->publishedPageQuery($normalized)->first();

        if ($page === null && $normalized === '/') {
            $this->ensureRootPage();
            $page = $this->publishedPageQuery($normalized)->first();
        }

        return $page;
    }

    protected function publishedPageQuery(string $path): \Illuminate\Database\Eloquent\Builder
    {
        return Page::query()
            ->where('path', $path)
            ->where('status', ContentStatus::Published)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function ensureRootPage(): Page
    {
        return Page::query()->firstOrCreate(
            ['path' => '/'],
            [
                'slug' => 'home',
                'title' => 'Home',
                'summary' => '',
                'body' => '',
                'status' => ContentStatus::Published,
                'published_at' => now(),
                'is_system' => true,
            ],
        );
    }
}

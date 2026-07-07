<?php

namespace App\Modules\Content\Requests\Concerns;

use App\Modules\Content\Models\Content;
use App\Modules\Content\Services\ContentUrlService;
use App\Modules\PageManager\Models\Page;

trait ValidatesContentPagePath
{
    protected function mergePublicPagePathForValidation(): void
    {
        $path = $this->resolvePublicPagePath();

        if ($path !== null) {
            $this->merge(['_public_page_path' => $path]);
        }
    }

    protected function resolvePublicPagePath(): ?string
    {
        if (! $this->filled('slug') || ! $this->filled('content_type')) {
            return null;
        }

        $content = Content::make([
            'content_type' => (string) $this->input('content_type'),
            'slug' => (string) $this->input('slug'),
        ]);

        $relative = app(ContentUrlService::class)->pathFor($content);

        if ($relative === null) {
            return null;
        }

        return '/'.$relative;
    }

    protected function syncedPageIdToIgnore(): ?int
    {
        return null;
    }

    /**
     * @return array<int, mixed>
     */
    protected function publicPagePathRules(): array
    {
        return [
            'nullable',
            'string',
            new \App\Rules\AvailablePagePath($this->syncedPageIdToIgnore()),
        ];
    }
}

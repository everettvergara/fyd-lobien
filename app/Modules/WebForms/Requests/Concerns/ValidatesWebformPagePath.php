<?php

namespace App\Modules\WebForms\Requests\Concerns;

use App\Modules\PageManager\Models\Page;

trait ValidatesWebformPagePath
{
    protected function mergePublicPagePathForValidation(): void
    {
        if (! $this->filled('slug')) {
            return;
        }

        $this->merge([
            '_public_page_path' => Page::normalizePath('/'.(string) $this->input('slug')),
        ]);
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

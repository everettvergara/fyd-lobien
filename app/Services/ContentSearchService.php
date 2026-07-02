<?php

namespace App\Services;

use App\Modules\Content\Models\Content;
use App\Support\ContentTypeRegistry;
use App\Support\PublicContent;
use Illuminate\Support\Collection;

class ContentSearchService
{
    public function __construct(
        protected ContentTypeRegistry $contentTypes,
    ) {}

    public function search(string $query, int $limit = 20): Collection
    {
        $query = trim($query);

        if (strlen($query) < 2) {
            return collect();
        }

        return Content::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('summary', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get()
            ->map(function (Content $content) {
                return array_merge(PublicContent::contentCard($content), [
                    'type' => 'content',
                    'typeLabel' => $this->contentTypes->label($content->content_type),
                ]);
            })
            ->values();
    }
}

<?php

namespace App\Modules\Content\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\Content\Services\ContentPageSyncService;
use App\Modules\PageManager\Models\Page;
use App\Support\PublicContent;

class ContentExtrasBlockResolver implements BlockResolver
{
    public function __construct(
        protected ContentPageSyncService $contentPages,
    ) {}

    public function resolve(array $config, Page $page): array
    {
        $content = $this->contentPages->contentForPage($page);

        if ($content === null) {
            return [
                'urlLink' => '',
                'attachment' => null,
                'galleryImages' => [],
            ];
        }

        $entry = PublicContent::entry($content);

        return [
            'urlLink' => (string) ($entry['urlLink'] ?? ''),
            'attachment' => $entry['attachment'],
            'galleryImages' => $entry['galleryImages'] ?? [],
        ];
    }
}

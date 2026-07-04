<?php

namespace App\Modules\ContentBlocks\Services;

use App\Modules\ContentBlocks\Models\ContentBlock;

class ContentBlockPreviewService
{
    public function __construct(
        protected ContentBlockService $blocks,
        protected ContentBlockQueryService $query,
        protected ContentBlockRenderingService $rendering,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{meta: array<string, mixed>, metaHtml: string, html: string, sql: array{countSql: string, dataSql: string}, sqlHtml: string}
     */
    public function retrieve(array $attributes, int $page = 1, ?ContentBlock $existing = null): array
    {
        $block = $this->blocks->makePreviewBlock($attributes, $existing);
        $page = max(1, $page);
        $totalMatching = $this->query->countMatching($block);
        $dto = $this->rendering->dto($block, $page);
        $retrieved = count($dto['rows']);
        $sql = $this->query->explainSql($block, $page);

        $meta = [
            'totalMatching' => $totalMatching,
            'retrieved' => $retrieved,
            'formatter' => $dto['formatter'],
            'paginationEnabled' => (bool) $block->pagination_enabled,
            'page' => $page,
            'lastPage' => $dto['pagination']['lastPage'] ?? 1,
            'perPage' => (int) $block->items_per_page,
        ];

        if (! $block->pagination_enabled) {
            $meta['limitedTo'] = (int) $block->items_per_page;
        }

        return [
            'meta' => $meta,
            'metaHtml' => view('contentblocks::content-blocks._preview-meta', ['meta' => $meta])->render(),
            'html' => view('contentblocks::content-blocks._preview-results', [
                'preview' => $dto,
            ])->render(),
            'sql' => $sql,
            'sqlHtml' => view('contentblocks::content-blocks._preview-sql', ['sql' => $sql])->render(),
        ];
    }
}

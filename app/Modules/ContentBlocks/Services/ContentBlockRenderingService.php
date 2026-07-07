<?php

namespace App\Modules\ContentBlocks\Services;

use App\Modules\Content\Models\Content;
use App\Modules\Content\Services\ContentUrlService;
use App\Modules\ContentBlocks\Models\ContentBlock;
use App\Modules\ContentBlocks\Support\ContentBlockFieldRegistry;
use App\Support\ContentTypeRegistry;
use App\Support\HtmlSanitizer;
use App\Support\PublicContent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ContentBlockRenderingService
{
    public function __construct(
        protected ContentBlockQueryService $query,
        protected ContentBlockFieldRegistry $fields,
        protected ContentUrlService $urls,
    ) {}

    public function contentBlockByKey(string $key, int $page = 1): ?array
    {
        $cacheKey = 'content_blocks.key.'.$key.'.page.'.$page;

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($key, $page) {
            $block = ContentBlock::query()
                ->published()
                ->where('key', $key)
                ->first();

            return $block ? $this->dto($block, $page) : null;
        });
    }

    public function dto(ContentBlock $block, int $page = 1): array
    {
        $results = $this->query->execute($block, $page);
        $fieldConfigs = $this->normalizedFieldConfigs($block);

        $rows = ($results instanceof LengthAwarePaginator ? $results->getCollection() : $results)
            ->map(fn (Content $content) => $this->mapRow($content, $fieldConfigs))
            ->values()
            ->all();

        $pagination = null;

        if ($results instanceof LengthAwarePaginator) {
            $pagination = [
                'currentPage' => $results->currentPage(),
                'lastPage' => $results->lastPage(),
                'perPage' => $results->perPage(),
                'total' => $results->total(),
                'queryParam' => $this->paginationQueryParam($block->key),
            ];
        }

        return [
            'key' => $block->key,
            'formatter' => $block->formatter->value,
            'wrapperClass' => $block->wrapper_class ?: 'content-block content-block--'.$block->key,
            'wrapperId' => $block->wrapper_id ?: 'content-block-'.$block->key,
            'fields' => collect($fieldConfigs)->map(fn (array $field) => [
                'field' => $field['field'],
                'label' => $field['label'],
                'class' => $field['class'],
                'id' => $field['id'],
            ])->values()->all(),
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }

    public function forgetCache(string $key): void
    {
        for ($page = 1; $page <= 20; $page++) {
            Cache::forget('content_blocks.key.'.$key.'.page.'.$page);
        }
    }

    public function paginationQueryParam(string $key): string
    {
        return 'cb_'.$key.'_page';
    }

    /**
     * @return list<array{field: string, label: string, class: string, id: string, sort_order: int, link_to_content: bool}>
     */
    protected function normalizedFieldConfigs(ContentBlock $block): array
    {
        $fields = collect($block->fields ?? [])
            ->filter(fn ($field) => ! empty($field['field']))
            ->sortBy(fn ($field) => (int) ($field['sort_order'] ?? 0))
            ->values()
            ->all();

        return array_map(function (array $field) use ($block) {
            $fieldKey = (string) $field['field'];

            return [
                'field' => $fieldKey,
                'label' => (string) ($field['label'] ?: $this->fields->defaultLabel($fieldKey)),
                'class' => (string) ($field['class'] ?: $this->fields->defaultClass($fieldKey)),
                'id' => (string) ($field['id'] ?: $this->fields->defaultId($block->key, $fieldKey)),
                'sort_order' => (int) ($field['sort_order'] ?? 0),
                'link_to_content' => (bool) ($field['link_to_content'] ?? false),
            ];
        }, $fields);
    }

    /**
     * @param  list<array{field: string, label: string, class: string, id: string, sort_order: int, link_to_content: bool}>  $fieldConfigs
     * @return list<array{field: string, value: mixed, class: string, id: string, label: string, linkToContent: bool, contentPath: ?string}>
     */
    protected function mapRow(Content $content, array $fieldConfigs): array
    {
        $contentPath = $this->urls->pathFor($content);

        return collect($fieldConfigs)->map(function (array $field) use ($content, $contentPath) {
            $linkToContent = (bool) ($field['link_to_content'] ?? false);

            return [
                'field' => $field['field'],
                'label' => $field['label'],
                'value' => $this->fieldValue($content, $field['field']),
                'class' => $field['class'],
                'id' => $field['id'],
                'linkToContent' => $linkToContent,
                'contentPath' => $linkToContent && $contentPath ? $contentPath : null,
            ];
        })->values()->all();
    }

    protected function fieldValue(Content $content, string $fieldKey): mixed
    {
        return match ($fieldKey) {
            'title' => $content->title,
            'slug' => $content->slug,
            'summary' => $content->summary,
            'body' => HtmlSanitizer::clean((string) $content->body),
            'content_type' => app(ContentTypeRegistry::class)->label($content->content_type),
            'published_at' => $content->published_at?->format('M j, Y'),
            'author.name' => $content->author?->name,
            'featured_image' => PublicContent::media($content->featuredImage),
            'url_link' => $content->url_link,
            'attachment' => PublicContent::file($content->attachment),
            default => null,
        };
    }
}

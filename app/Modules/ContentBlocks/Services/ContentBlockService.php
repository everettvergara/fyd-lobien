<?php

namespace App\Modules\ContentBlocks\Services;

use App\Modules\ContentBlocks\Models\ContentBlock;
use App\Modules\ContentBlocks\Support\ContentBlockFieldRegistry;
use Illuminate\Support\Arr;

class ContentBlockService
{
    public function __construct(
        protected ContentBlockFieldRegistry $fields,
        protected ContentBlockRenderingService $rendering,
    ) {}

    public function create(array $attributes): ContentBlock
    {
        $block = ContentBlock::create($this->normalizeAttributes($attributes));

        return $block;
    }

    public function update(ContentBlock $block, array $attributes): ContentBlock
    {
        $block->update($this->normalizeAttributes($attributes, $block));

        $this->rendering->forgetCache($block->key);

        return $block->fresh();
    }

    public function delete(ContentBlock $block): void
    {
        $this->rendering->forgetCache($block->key);
        $block->delete();
    }

    public function makePreviewBlock(array $attributes, ?ContentBlock $existing = null): ContentBlock
    {
        return (new ContentBlock)->forceFill(
            $this->normalizeAttributes($attributes, $existing),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizeAttributes(array $attributes, ?ContentBlock $existing = null): array
    {
        $key = (string) ($attributes['key'] ?? $existing?->key ?? '');

        $attributes['content_types'] = array_values(array_filter(
            Arr::wrap($attributes['content_types'] ?? []),
            fn ($type) => is_string($type) && $type !== '',
        ));

        $attributes['fields'] = $this->normalizeFields(
            Arr::wrap($attributes['fields'] ?? []),
            $key,
        );

        $attributes['filters'] = $this->normalizeFilters(Arr::wrap($attributes['filters'] ?? []));
        $attributes['pagination_enabled'] = (bool) ($attributes['pagination_enabled'] ?? false);
        $attributes['items_per_page'] = max(1, (int) ($attributes['items_per_page'] ?? 10));

        return Arr::only($attributes, [
            'key',
            'name',
            'icon',
            'status',
            'content_types',
            'fields',
            'filters',
            'sort_field',
            'sort_direction',
            'items_per_page',
            'pagination_enabled',
            'formatter',
            'wrapper_class',
            'wrapper_id',
            'settings',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @return list<array<string, mixed>>
     */
    protected function normalizeFields(array $fields, string $blockKey): array
    {
        $normalized = [];

        foreach ($fields as $index => $field) {
            $fieldKey = (string) ($field['field'] ?? '');

            if ($fieldKey === '' || ! $this->fields->has($fieldKey)) {
                continue;
            }

            $normalized[] = [
                'field' => $fieldKey,
                'label' => (string) ($field['label'] ?? $this->fields->defaultLabel($fieldKey)),
                'class' => (string) ($field['class'] ?? $this->fields->defaultClass($fieldKey)),
                'id' => (string) ($field['id'] ?? $this->fields->defaultId($blockKey, $fieldKey)),
                'sort_order' => (int) ($field['sort_order'] ?? $index),
            ];
        }

        usort($normalized, fn (array $a, array $b) => $a['sort_order'] <=> $b['sort_order']);

        return $normalized;
    }

    /**
     * @param  list<array<string, mixed>>  $filters
     * @return list<array<string, mixed>>
     */
    protected function normalizeFilters(array $filters): array
    {
        $normalized = [];

        foreach ($filters as $filter) {
            $field = (string) ($filter['field'] ?? '');
            $operator = (string) ($filter['operator'] ?? '');

            if ($field === '' || $operator === '' || ! $this->fields->has($field)) {
                continue;
            }

            $value = $filter['value'] ?? null;

            if (in_array($operator, ['in', 'not_in'], true) && is_string($value)) {
                $value = array_values(array_filter(array_map('trim', explode(',', $value))));
            }

            $normalized[] = [
                'field' => $field,
                'operator' => $operator,
                'value' => $value,
                'group' => 'and',
            ];
        }

        return $normalized;
    }
}

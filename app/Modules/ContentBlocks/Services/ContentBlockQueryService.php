<?php

namespace App\Modules\ContentBlocks\Services;

use App\Modules\Content\Models\Content;
use App\Modules\ContentBlocks\Models\ContentBlock;
use App\Modules\ContentBlocks\Support\ContentBlockFieldRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ContentBlockQueryService
{
    public function __construct(
        protected ContentBlockFieldRegistry $fields,
    ) {}

    public function execute(ContentBlock $block, int $page = 1): LengthAwarePaginator|Collection
    {
        $fieldKeys = collect($block->fields ?? [])->pluck('field')->filter()->all();
        $relations = $this->fields->relationsFor($fieldKeys);

        $query = $this->applySort($this->baseQuery($block)->with($relations), $block);

        $perPage = max(1, (int) $block->items_per_page);

        if ($block->pagination_enabled) {
            $pageName = 'cb_'.$block->key.'_page';

            return $query->paginate($perPage, ['*'], $pageName, max(1, $page));
        }

        return $query->limit($perPage)->get();
    }

    public function countMatching(ContentBlock $block): int
    {
        return $this->baseQuery($block)->count();
    }

    /**
     * @return array{countSql: string, dataSql: string}
     */
    public function explainSql(ContentBlock $block, int $page = 1): array
    {
        $page = max(1, $page);
        $perPage = max(1, (int) $block->items_per_page);

        $countSql = (clone $this->baseQuery($block))
            ->selectRaw('count(*) as aggregate')
            ->toRawSql();

        $dataQuery = $this->applySort($this->baseQuery($block), $block);

        if ($block->pagination_enabled) {
            $dataQuery->limit($perPage)->offset(($page - 1) * $perPage);
        } else {
            $dataQuery->limit($perPage);
        }

        return [
            'countSql' => $countSql,
            'dataSql' => $dataQuery->toRawSql(),
        ];
    }

    protected function applySort(Builder $query, ContentBlock $block): Builder
    {
        $sortField = $block->sort_field ?: 'published_at';
        $sortMeta = $this->fields->meta($sortField);
        $column = $sortMeta['column'] ?? 'published_at';
        $direction = strtolower((string) $block->sort_direction) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($column, $direction);
    }

    protected function baseQuery(ContentBlock $block): Builder
    {
        $query = Content::query()->published();

        $types = array_values(array_filter($block->content_types ?? []));

        if ($types !== []) {
            $query->whereIn('content_type', $types);
        }

        foreach ($block->filters ?? [] as $filter) {
            $this->applyFilter($query, $filter);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    protected function applyFilter(Builder $query, array $filter): void
    {
        $field = (string) ($filter['field'] ?? '');
        $operator = (string) ($filter['operator'] ?? '');
        $value = $filter['value'] ?? null;

        if ($field === '' || $operator === '' || ! $this->fields->has($field)) {
            return;
        }

        if ($field === 'author.name') {
            $this->applyAuthorFilter($query, $operator, $value);

            return;
        }

        if ($field === 'featured_image') {
            $this->applyFeaturedImageFilter($query, $operator);

            return;
        }

        $meta = $this->fields->meta($field);
        $column = $meta['column'] ?? null;

        if ($column === null) {
            return;
        }

        match ($operator) {
            'equals' => $query->where($column, '=', $value),
            'not_equals' => $query->where($column, '!=', $value),
            'contains' => $query->where($column, 'like', '%'.$value.'%'),
            'starts_with' => $query->where($column, 'like', $value.'%'),
            'is_empty' => $query->where(function (Builder $inner) use ($column) {
                $inner->whereNull($column)->orWhere($column, '=', '');
            }),
            'is_not_empty' => $query->where(function (Builder $inner) use ($column) {
                $inner->whereNotNull($column)->where($column, '!=', '');
            }),
            'in' => $query->whereIn($column, $this->normalizeListValue($value)),
            'not_in' => $query->whereNotIn($column, $this->normalizeListValue($value)),
            'before' => $query->whereDate($column, '<', $value),
            'after' => $query->whereDate($column, '>', $value),
            'on' => $query->whereDate($column, '=', $value),
            default => null,
        };
    }

    protected function applyAuthorFilter(Builder $query, string $operator, mixed $value): void
    {
        match ($operator) {
            'equals' => $query->whereHas('author', fn (Builder $author) => $author->where('name', '=', $value)),
            'contains' => $query->whereHas('author', fn (Builder $author) => $author->where('name', 'like', '%'.$value.'%')),
            default => null,
        };
    }

    protected function applyFeaturedImageFilter(Builder $query, string $operator): void
    {
        match ($operator) {
            'is_empty' => $query->whereNull('featured_image_id'),
            'is_not_empty' => $query->whereNotNull('featured_image_id'),
            default => null,
        };
    }

    /**
     * @return list<string>
     */
    protected function normalizeListValue(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, fn ($item) => $item !== null && $item !== ''));
        }

        if (is_string($value) && str_contains($value, ',')) {
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return $value === null || $value === '' ? [] : [(string) $value];
    }
}

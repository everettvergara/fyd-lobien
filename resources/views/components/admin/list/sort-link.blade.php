@props([
    'result',
    'column',
])

@php
    $active = $result->state->sort === $column->key;
    $direction = $active && $result->state->direction === 'asc' ? 'desc' : 'asc';
    $query = array_merge($result->state->query(), [
        'sort' => $column->key,
        'direction' => $direction,
        'page' => null,
    ]);
    $icon = $active
        ? ($result->state->direction === 'asc' ? 'bi-sort-up' : 'bi-sort-down')
        : 'bi-arrow-down-up';
@endphp

<a href="{{ request()->url() }}?{{ http_build_query(array_filter($query, fn ($value) => $value !== null && $value !== '')) }}" class="text-decoration-none text-reset">
    {{ $column->label }}
    <i class="{{ admin_icon($icon) }} ms-1 small" aria-hidden="true"></i>
</a>

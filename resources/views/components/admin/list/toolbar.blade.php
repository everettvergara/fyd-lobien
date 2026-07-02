@props([
    'result',
    'resetRoute' => url()->current(),
])

<div class="p-3 admin-list-toolbar">
    <form method="GET" class="d-flex flex-wrap gap-3 align-items-end" data-admin-list-search-form>
        @if ($result->definition->hasSearch())
            <div class="admin-list-filter-field">
                <label class="form-label" for="{{ $result->definition->id }}-search">Search</label>
                <input
                    id="{{ $result->definition->id }}-search"
                    type="search"
                    name="search"
                    value="{{ $result->state->search }}"
                    class="form-control"
                    placeholder="{{ $result->definition->searchPlaceholder ?? 'Search...' }}"
                    data-admin-list-search
                >
            </div>
        @endif

        @foreach ($result->definition->filters as $filter)
            <div class="admin-list-filter-field">
                <label class="form-label" for="{{ $result->definition->id }}-{{ $filter->key }}">{{ $filter->label }}</label>
                @if ($filter->type === 'select')
                    <select id="{{ $result->definition->id }}-{{ $filter->key }}" name="{{ $filter->key }}" class="form-select">
                        <option value="">All</option>
                        @foreach ($filter->options() as $value => $label)
                            <option value="{{ $value }}" @selected(($result->state->filters[$filter->key] ?? '') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                @else
                    <input
                        id="{{ $result->definition->id }}-{{ $filter->key }}"
                        type="{{ $filter->type }}"
                        name="{{ $filter->key }}"
                        value="{{ $result->state->filters[$filter->key] ?? '' }}"
                        class="form-control"
                    >
                @endif
            </div>
        @endforeach

        <div class="admin-list-filter-field">
            <label class="form-label" for="{{ $result->definition->id }}-per-page">Per page</label>
            <select id="{{ $result->definition->id }}-per-page" name="per_page" class="form-select">
                @foreach ($result->definition->perPageOptions as $option)
                    <option value="{{ $option }}" @selected($result->state->perPage === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>

        <input type="hidden" name="sort" value="{{ $result->state->sort }}">
        <input type="hidden" name="direction" value="{{ $result->state->direction }}">

        <div class="admin-list-filter-actions d-flex gap-2">
            <button type="submit" class="btn btn-outline-primary">
                {{ $result->definition->hasFilters() ? 'Apply' : 'Search' }}
            </button>
            <a href="{{ $resetRoute }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

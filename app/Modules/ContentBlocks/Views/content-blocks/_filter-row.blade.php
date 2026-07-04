@php
    $operators = app(\App\Modules\ContentBlocks\Support\ContentBlockFilterOperators::class);
    $registry = app(\App\Modules\ContentBlocks\Support\ContentBlockFieldRegistry::class);
    $selectedField = (string) ($filter['field'] ?? '');
    $selectedOperator = (string) ($filter['operator'] ?? '');
    $availableOperators = $selectedField !== '' ? $operators->forField($selectedField, $registry) : [];
@endphp

<div class="filter-row border rounded p-3 mb-2">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Field</label>
            <select name="filters[{{ $index }}][field]" class="form-select form-select-sm filter-field-select">
                <option value="">Select field</option>
                @foreach ($fieldOptions as $value => $label)
                    <option value="{{ $value }}" @selected($selectedField === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Operator</label>
            <select name="filters[{{ $index }}][operator]" class="form-select form-select-sm filter-operator-select">
                <option value="">Select operator</option>
                @foreach ($availableOperators as $operator)
                    <option value="{{ $operator }}" @selected($selectedOperator === $operator)>{{ $operators->labels()[$operator] ?? $operator }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label small">Value</label>
            <input type="text" name="filters[{{ $index }}][value]" class="form-control form-control-sm" value="{{ is_array($filter['value'] ?? null) ? implode(', ', $filter['value']) : ($filter['value'] ?? '') }}">
            <div class="form-text">Use comma-separated values for "is one of" filters.</div>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-outline-danger btn-sm remove-filter-row">&times;</button>
        </div>
    </div>
</div>

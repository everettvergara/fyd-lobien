@props([
    'result',
    'bulkFormId' => null,
])

@php
    $columns = $result->definition->visibleColumns();
    $hasActions = count($result->definition->rowActions) > 0;
    $hasSelection = $result->definition->selectable && count($result->definition->bulkActions) > 0;
    $colspan = count($columns) + ($hasSelection ? 1 : 0) + ($hasActions ? 1 : 0);
@endphp

<div class="table-responsive">
    <table class="table table-borderless table-striped table-hover align-middle mb-0 admin-list-table">
        <thead class="sticky-top">
            <tr>
                @if ($hasSelection)
                    <th style="width: 42px;">
                        <input type="checkbox" class="form-check-input" aria-label="Select all rows" data-admin-list-select-all>
                    </th>
                @endif

                @foreach ($columns as $column)
                    <th class="{{ $column->headerClass }}">
                        @if ($column->sortable())
                            <x-admin.list.sort-link :result="$result" :column="$column" />
                        @else
                            {{ $column->label }}
                        @endif
                    </th>
                @endforeach

                @if ($hasActions)
                    <th class="text-end">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($result->records as $record)
                @php $rowNumber = $result->records->firstItem() + $loop->index; @endphp
                <tr>
                    @if ($hasSelection)
                        <td>
                            <input
                                type="checkbox"
                                name="selected[]"
                                value="{{ $record->getKey() }}"
                                class="form-check-input"
                                aria-label="Select row {{ $rowNumber }}"
                                @if ($bulkFormId) form="{{ $bulkFormId }}" @endif
                                data-admin-list-row-checkbox
                            >
                        </td>
                    @endif

                    @foreach ($columns as $column)
                        @php $value = $column->valueFor($record, $rowNumber); @endphp
                        <td class="{{ $column->class }}">
                            @if ($column->raw)
                                {!! $value !!}
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    @endforeach

                    @if ($hasActions)
                        <td class="text-end text-nowrap">
                            <x-admin.list.row-actions :actions="$result->definition->rowActions" :record="$record" />
                        </td>
                    @endif
                </tr>
            @empty
                <x-admin.empty-state :colspan="$colspan" message="No records found." />
            @endforelse
        </tbody>
    </table>
</div>

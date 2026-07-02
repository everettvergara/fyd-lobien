@props([
    'result',
    'bulkRoute' => null,
    'resetRoute' => url()->current(),
])

<x-admin.card :padding="false" class="admin-list-card">
    <x-admin.list.toolbar :result="$result" :reset-route="$resetRoute" />

    @if ($bulkRoute && count($result->definition->bulkActions))
        @php $bulkFormId = $result->definition->id.'-bulk-form'; @endphp

        <form id="{{ $bulkFormId }}" method="POST" action="{{ $bulkRoute }}">
            @csrf
        </form>

        <div class="p-3 admin-list-toolbar d-flex flex-wrap gap-2 align-items-center" data-admin-list-bulk-form>
            <select name="bulk_action" form="{{ $bulkFormId }}" class="form-select w-auto" data-admin-list-bulk-action required>
                <option value="">Bulk actions</option>
                @foreach ($result->definition->bulkActions as $action)
                    <option value="{{ $action->key }}" data-confirm="{{ $action->confirm }}">{{ $action->label }}</option>
                @endforeach
            </select>
            @foreach ($result->definition->bulkActions as $action)
                @if ($action->hasInput())
                    <select
                        name="{{ $action->inputName }}"
                        form="{{ $bulkFormId }}"
                        class="form-select w-auto"
                        data-admin-list-bulk-input
                        data-bulk-action="{{ $action->key }}"
                        aria-label="{{ $action->inputLabel ?? $action->inputName }}"
                        hidden
                        disabled
                    >
                        <option value="">{{ $action->inputLabel ?? 'Select...' }}</option>
                        @foreach ($action->inputOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @endif
            @endforeach
            <button type="submit" form="{{ $bulkFormId }}" class="btn btn-outline-primary" data-admin-list-bulk-submit disabled>Apply</button>
            <span class="small text-muted" data-admin-list-selected-count>0 selected</span>
        </div>

        <x-admin.list.table :result="$result" :bulk-form-id="$bulkFormId" />
    @else
        <x-admin.list.table :result="$result" />
    @endif

    @if ($result->records->hasPages())
        <x-slot:footer>
            <x-admin.list.pagination :result="$result" />
        </x-slot:footer>
    @endif
</x-admin.card>

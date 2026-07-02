@props([
    'type' => 'empty',
    'message' => 'No records found.',
])

<div class="text-center text-muted py-4" data-admin-list-state="{{ $type }}">
    @if ($type === 'loading')
        <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
    @endif
    {{ $message }}
</div>

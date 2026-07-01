@props([
    'title',
    'createRoute' => null,
    'createLabel' => null,
    'createModel' => null,
    'backRoute' => null,
    'backLabel' => 'Back',
])

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ $title }}</h1>
    <div class="d-flex gap-2 align-items-center">
        @if ($backRoute)
            <a href="{{ $backRoute }}" class="btn btn-outline-secondary btn-sm">{{ $backLabel }}</a>
        @endif

        @isset($actions)
            {{ $actions }}
        @endisset

        @if ($createRoute && ($createModel === null || auth()->user()?->can('create', $createModel)))
            <a href="{{ $createRoute }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> {{ $createLabel ?? 'Add' }}
            </a>
        @endif
    </div>
</div>

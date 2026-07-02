@props([
    'title',
    'createRoute' => null,
    'createLabel' => null,
    'createModel' => null,
    'backRoute' => null,
    'backLabel' => 'Back',
])

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">{{ $title }}</h1>
    <div class="d-flex gap-2 align-items-center">
        @if ($backRoute)
            <a href="{{ $backRoute }}" class="btn btn-outline-secondary">{{ $backLabel }}</a>
        @endif

        @isset($actions)
            {{ $actions }}
        @endisset

        @if ($createRoute && ($createModel === null || auth()->user()?->can('create', $createModel)))
            <a href="{{ $createRoute }}" class="btn btn-primary">
                <i class="{{ admin_icon('bi-plus-lg') }} me-1"></i> {{ $createLabel ?? 'Add' }}
            </a>
        @endif
    </div>
</div>

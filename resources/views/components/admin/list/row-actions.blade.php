@props([
    'actions',
    'record',
])

<div class="d-inline-flex gap-1 justify-content-end">
    @foreach ($actions as $action)
        @continue(! $action->visibleFor($record))

        @php
            $classes = $action->danger ? 'admin-icon-btn admin-icon-btn-danger' : 'admin-icon-btn';
            $method = $action->method();
        @endphp

        @if ($method === 'GET')
            <a
                href="{{ $action->urlFor($record) }}"
                class="btn {{ $classes }}"
                title="{{ $action->label }}"
                aria-label="{{ $action->label }}"
                @if ($action->newTab) target="_blank" rel="noopener" @endif
            >
                <i class="{{ admin_icon($action->icon) }}" aria-hidden="true"></i>
            </a>
        @else
            <form method="POST" action="{{ $action->urlFor($record) }}" class="d-inline" @if ($action->confirm) onsubmit="return confirm('{{ $action->confirm }}')" @endif>
                @csrf
                @if (! in_array($method, ['POST'], true))
                    @method($method)
                @endif
                <button type="submit" class="btn {{ $classes }}" title="{{ $action->label }}" aria-label="{{ $action->label }}">
                    <i class="{{ admin_icon($action->icon) }}" aria-hidden="true"></i>
                </button>
            </form>
        @endif
    @endforeach
</div>

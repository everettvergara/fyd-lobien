@props(['padding' => true, 'title' => null])

<div {{ $attributes->class(['card']) }}>
    @if (isset($header) || $title)
        <div class="card-header">{{ $header ?? $title }}</div>
    @endif

    @if ($padding)
        <div class="card-body">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif

    @isset($footer)
        <div class="card-footer">{{ $footer }}</div>
    @endisset
</div>

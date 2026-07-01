@props(['padding' => true])

<div {{ $attributes->class(['card']) }}>
    @isset($header)
        <div class="card-header">{{ $header }}</div>
    @endisset

    @if ($padding)
        <div class="card-body">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif

    @isset($footer)
        <div class="card-footer bg-white">{{ $footer }}</div>
    @endisset
</div>

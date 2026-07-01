@props(['items' => []])

@if (!empty($items))
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            @foreach ($items as $breadcrumb)
                @if ($loop->last)
                    <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['label'] }}</li>
                @else
                    <li class="breadcrumb-item">
                        <a href="{{ $breadcrumb['url'] ?? '#' }}">{{ $breadcrumb['label'] }}</a>
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif

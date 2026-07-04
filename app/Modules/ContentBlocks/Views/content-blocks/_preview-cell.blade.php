@php
    $cell = $cell ?? [];
    $value = $cell['value'] ?? null;
    $field = $cell['field'] ?? '';
@endphp

@if ($field === 'featured_image' && is_array($value) && ! empty($value['url']))
    <img src="{{ $value['url'] }}" alt="{{ $value['alt'] ?? '' }}" class="content-block-preview-image" style="max-width: 80px; max-height: 48px; object-fit: cover;">
@elseif ($field === 'body' && is_string($value) && $value !== '')
    <div class="content-block-preview-body">{!! $value !!}</div>
@elseif (is_array($value))
    <span class="text-muted">—</span>
@else
    {{ $value ?: '—' }}
@endif

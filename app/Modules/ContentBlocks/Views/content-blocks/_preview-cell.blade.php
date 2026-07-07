@php
    $cell = $cell ?? [];
    $value = $cell['value'] ?? null;
    $field = $cell['field'] ?? '';
    $linkToContent = ! empty($cell['linkToContent']) && ! empty($cell['contentPath']);
    $contentHref = $linkToContent ? '/'.$cell['contentPath'] : null;
    $featuredImageUrl = is_array($value)
        ? ($value['previewUrl'] ?? $value['url'] ?? null)
        : null;
@endphp

@if ($linkToContent && $field === 'featured_image' && is_array($value) && ! empty($featuredImageUrl))
    <a href="{{ $contentHref }}" class="content-block-preview-content-link">
        <img src="{{ $featuredImageUrl }}" alt="{{ $value['alt'] ?? '' }}" class="content-block-preview-image" style="max-width: 80px; max-height: 48px; object-fit: cover;">
    </a>
@elseif ($linkToContent && $field === 'attachment' && is_array($value) && ! empty($value['label']))
    <a href="{{ $contentHref }}" class="content-block-preview-content-link">{{ $value['label'] }}</a>
@elseif ($linkToContent && $field === 'body' && is_string($value) && $value !== '')
    <a href="{{ $contentHref }}" class="content-block-preview-content-link">
        <div class="content-block-preview-body">{!! $value !!}</div>
    </a>
@elseif ($linkToContent && ! is_array($value) && $value !== '' && $value !== null)
    <a href="{{ $contentHref }}" class="content-block-preview-content-link">{{ $value }}</a>
@elseif ($field === 'featured_image' && is_array($value) && ! empty($featuredImageUrl))
    <img src="{{ $featuredImageUrl }}" alt="{{ $value['alt'] ?? '' }}" class="content-block-preview-image" style="max-width: 80px; max-height: 48px; object-fit: cover;">
@elseif ($field === 'body' && is_string($value) && $value !== '')
    <div class="content-block-preview-body">{!! $value !!}</div>
@elseif (is_array($value))
    <span class="text-muted">—</span>
@else
    {{ $value ?: '—' }}
@endif

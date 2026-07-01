<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title }} — Preview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="bg-warning text-dark py-2 text-center small">Preview Mode — {{ $page->status->label() }}</div>
    <div class="container py-5">
        <h1>{{ $page->title }}</h1>
        @if ($page->summary)<p class="lead text-muted">{{ $page->summary }}</p>@endif
        @if ($page->content)<div class="mb-5">{!! nl2br(e($page->content)) !!}</div>@endif
        @foreach ($page->sections as $section)
            <div class="card mb-3"><div class="card-body">
                <span class="badge bg-secondary mb-2">{{ str_replace('_', ' ', $section->component_type) }}</span>
                @if (!empty($section->settings['title']))<h5>{{ $section->settings['title'] }}</h5>@endif
            </div></div>
        @endforeach
    </div>
</body>
</html>

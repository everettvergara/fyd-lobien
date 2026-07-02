<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $content->title }} — Preview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="bg-warning text-dark py-2 text-center small">Preview Mode — {{ $content->status->label() }}</div>
    <div class="container py-5">
        <p class="text-muted small">{{ app(\App\Support\ContentTypeRegistry::class)->label($content->content_type) }}</p>
        <h1>{{ $content->title }}</h1>
        @if ($content->summary)<p class="lead text-muted">{{ $content->summary }}</p>@endif
        @if ($content->body)<div class="mb-5 content-body">{!! $content->body !!}</div>@endif
    </div>
</body>
</html>

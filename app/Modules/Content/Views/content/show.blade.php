@extends('admin.layouts.app')
@section('title', $content->title)
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Content', 'url' => route('admin.content.index')], ['label' => $content->title]]; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ $content->title }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.content.preview', $content) }}" class="btn btn-outline-secondary" target="_blank">Preview</a>
        @can('update', $content)<a href="{{ route('admin.content.edit', $content) }}" class="btn btn-primary">Edit</a>@endcan
    </div>
</div>
<div class="card"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3 text-muted">Type</dt><dd class="col-sm-9">{{ app(\App\Support\ContentTypeRegistry::class)->label($content->content_type) }}</dd>
        <dt class="col-sm-3 text-muted">Slug</dt><dd class="col-sm-9">{{ $content->slug }}</dd>
        <dt class="col-sm-3 text-muted">Status</dt><dd class="col-sm-9"><span class="badge bg-primary-subtle text-primary">{{ $content->status->label() }}</span></dd>
        <dt class="col-sm-3 text-muted">Author</dt><dd class="col-sm-9">{{ $content->author->name }}</dd>
        @if ($content->seoMeta)
            <dt class="col-sm-3 text-muted">SEO Title</dt><dd class="col-sm-9">{{ $content->seoMeta->seo_title ?? '—' }}</dd>
            <dt class="col-sm-3 text-muted">Sitemap</dt><dd class="col-sm-9">{{ ($content->seoMeta->sitemap_include ?? true) ? 'Included' : 'Excluded' }}</dd>
            <dt class="col-sm-3 text-muted">Change Frequency</dt><dd class="col-sm-9">{{ $content->seoMeta->sitemap_changefreq ?? 'Default' }}</dd>
            <dt class="col-sm-3 text-muted">Priority</dt><dd class="col-sm-9">{{ $content->seoMeta->sitemap_priority ?? 'Default' }}</dd>
        @endif
    </dl>
</div></div>
@endsection

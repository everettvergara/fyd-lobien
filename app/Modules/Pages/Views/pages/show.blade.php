@extends('admin.layouts.app')
@section('title', $page->title)
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Pages', 'url' => route('admin.pages.index')], ['label' => $page->title]]; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ $page->title }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.pages.preview', $page) }}" class="btn btn-outline-secondary btn-sm" target="_blank">Preview</a>
        @can('update', $page)<a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
    </div>
</div>
<div class="card"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3 text-muted">Slug</dt><dd class="col-sm-9">{{ $page->slug }}</dd>
        <dt class="col-sm-3 text-muted">Status</dt><dd class="col-sm-9"><span class="badge bg-primary-subtle text-primary">{{ $page->status->label() }}</span></dd>
        <dt class="col-sm-3 text-muted">Author</dt><dd class="col-sm-9">{{ $page->author->name }}</dd>
        <dt class="col-sm-3 text-muted">Sections</dt><dd class="col-sm-9">{{ $page->sections->count() }}</dd>
        @if ($page->seoMeta)
            <dt class="col-sm-3 text-muted">SEO Title</dt><dd class="col-sm-9">{{ $page->seoMeta->seo_title ?? '—' }}</dd>
        @endif
    </dl>
</div></div>
@endsection

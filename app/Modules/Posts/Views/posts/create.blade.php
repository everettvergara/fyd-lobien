@extends('admin.layouts.app')
@section('title', isset($post) ? 'Edit Post' : 'Create Post')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ isset($post) ? 'Edit Post' : 'Create Post' }}</h1>
    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
</div>
<div class="card"><div class="card-body">
<form method="POST" action="{{ isset($post) ? route('admin.posts.update', $post) : route('admin.posts.store') }}">
@csrf @if(isset($post))@method('PUT')@endif
<div class="mb-3"><label class="form-label">Title</label><input type="text" class="form-control" name="title" value="{{ old('title', $post?->title) }}" required></div>
<div class="mb-3"><label class="form-label">Slug</label><input type="text" class="form-control" name="slug" value="{{ old('slug', $post?->slug) }}" required></div>
<div class="mb-3"><label class="form-label">Excerpt</label><textarea class="form-control" name="excerpt" rows="2">{{ old('excerpt', $post?->excerpt) }}</textarea></div>
<div class="mb-3"><label class="form-label">Content</label><textarea class="form-control" name="content" rows="8">{{ old('content', $post?->content) }}</textarea></div>
<div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="status">@foreach($statuses as $s)<option value="{{ $s->value }}" {{ old('status', $post?->status?->value)===$s->value?'selected':'' }}>{{ $s->label() }}</option>@endforeach</select></div>
<hr>@include('seo::partials.seo-fields', ['seo' => $post?->seoMeta])
<button type="submit" class="btn btn-primary mt-3">{{ isset($post) ? 'Save' : 'Create' }}</button>
</form></div></div>
@endsection

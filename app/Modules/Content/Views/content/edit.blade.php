@extends('admin.layouts.app')
@section('title', 'Edit Content')
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Content', 'url' => route('admin.content.index')], ['label' => $content->title]]; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Content</h1>
    <div class="d-flex gap-2">
        @can('publish', $content)
            @if ($content->status !== App\Enums\ContentStatus::Published)
                <form method="POST" action="{{ route('admin.content.publish', $content) }}">@csrf<button class="btn btn-success">Publish</button></form>
            @endif
        @endcan
        <form method="POST" action="{{ route('admin.content.duplicate', $content) }}">@csrf<button class="btn btn-outline-secondary">Duplicate</button></form>
        <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</div>
<div class="card border-0 shadow-none">
    <div class="card-body p-0">
        <form method="POST" action="{{ route('admin.content.update', $content) }}">@csrf @method('PUT')
            @include('content::content._form')
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>
@endsection

@extends('admin.layouts.app')
@section('title', 'Edit Page')
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Pages', 'url' => route('admin.pages.index')], ['label' => $page->title]]; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Page</h1>
    <div class="d-flex gap-2">
        @can('publish', $page)
            @if ($page->status !== App\Enums\ContentStatus::Published)
                <form method="POST" action="{{ route('admin.pages.publish', $page) }}">@csrf<button class="btn btn-success">Publish</button></form>
            @endif
        @endcan
        @if (! $page->is_system)
            <a href="{{ $page->path === '/' ? url('/') : url(ltrim($page->path, '/')) }}" class="btn btn-outline-primary" target="_blank">View</a>
        @else
            <a href="{{ url('/') }}" class="btn btn-outline-primary" target="_blank">View</a>
        @endif
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</div>

<div class="card border-0 shadow-none">
    <div class="card-body p-0">
        <form method="POST" action="{{ route('admin.pages.update', $page) }}" id="page-form">@csrf @method('PUT')
            @include('pagemanager::pages._form')
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>
@endsection

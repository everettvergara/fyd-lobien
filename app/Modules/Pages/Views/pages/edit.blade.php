@extends('admin.layouts.app')
@section('title', 'Edit Page')
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Pages', 'url' => route('admin.pages.index')], ['label' => $page->title]]; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Page</h1>
    <div class="d-flex gap-2">
        @can('publish', $page)
            @if ($page->status !== App\Enums\ContentStatus::Published)
                <form method="POST" action="{{ route('admin.pages.publish', $page) }}">@csrf<button class="btn btn-success btn-sm">Publish</button></form>
            @endif
        @endcan
        <form method="POST" action="{{ route('admin.pages.duplicate', $page) }}">@csrf<button class="btn btn-outline-secondary btn-sm">Duplicate</button></form>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>
</div>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('admin.pages.update', $page) }}">@csrf @method('PUT')
        @include('pages::pages._form')
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div></div>
@endsection

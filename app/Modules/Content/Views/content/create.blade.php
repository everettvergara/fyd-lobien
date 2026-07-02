@extends('admin.layouts.app')
@section('title', 'Create Content')
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Content', 'url' => route('admin.content.index')], ['label' => 'Create']]; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Create Content</h1>
    <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
<div class="card border-0 shadow-none">
    <div class="card-body p-0">
        <form method="POST" action="{{ route('admin.content.store') }}">@csrf
            @include('content::content._form', ['content' => null, 'defaultContentType' => $defaultContentType ?? null])
            <button type="submit" class="btn btn-primary">Create Content</button>
        </form>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Edit Content Type')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Content Types', 'url' => route('admin.content-types.index')],
        ['label' => $contentType->label],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Content Type</h1>
        <div class="d-flex gap-2">
            @can('create', App\Modules\Content\Models\Content::class)
                @if ($contentType->is_active)
                    <a href="{{ route('admin.content.create', ['content_type' => $contentType->key]) }}" class="btn btn-outline-primary">Add Content</a>
                @endif
            @endcan
            <a href="{{ route('admin.content-types.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.content-types.update', $contentType) }}">
                        @csrf
                        @method('PUT')
                        @include('content::content-types._form')
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

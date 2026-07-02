@extends('admin.layouts.app')

@section('title', 'Create Content Type')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Content Types', 'url' => route('admin.content-types.index')],
        ['label' => 'Create'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Create Content Type</h1>
        <a href="{{ route('admin.content-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.content-types.store') }}">
                        @csrf
                        @include('content::content-types._form', ['contentType' => null])
                        <button type="submit" class="btn btn-primary">Create Content Type</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

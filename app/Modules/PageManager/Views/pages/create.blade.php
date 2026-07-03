@extends('admin.layouts.app')
@section('title', 'Create Page')
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Pages', 'url' => route('admin.pages.index')], ['label' => 'Create']]; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Create Page</h1>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@if (! $masterConfigured)
    <div class="alert alert-warning">
        Configure <a href="{{ route('admin.page-master.edit') }}">Page Master</a> defaults before publishing pages.
    </div>
@endif

<div class="card border-0 shadow-none">
    <div class="card-body p-0">
        <form method="POST" action="{{ route('admin.pages.store') }}" id="page-form">@csrf
            @include('pagemanager::pages._form')
            <button type="submit" class="btn btn-primary">Create Page</button>
        </form>
    </div>
</div>
@endsection

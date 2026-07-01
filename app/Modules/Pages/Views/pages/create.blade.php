@extends('admin.layouts.app')
@section('title', 'Create Page')
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Pages', 'url' => route('admin.pages.index')], ['label' => 'Create']]; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Create Page</h1>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
</div>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('admin.pages.store') }}">@csrf
        @include('pages::pages._form', ['page' => null])
        <button type="submit" class="btn btn-primary">Create Page</button>
    </form>
</div></div>
@endsection

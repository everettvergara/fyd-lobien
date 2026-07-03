@extends('admin.layouts.app')

@section('title', 'Create Demo Tag')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Demo Tags', 'url' => route('admin.demo-tags.index')],
        ['label' => 'Create'],
    ]" />

    <x-admin.page-header title="Create Demo Tag" />

    <form method="POST" action="{{ route('admin.demo-tags.store') }}" class="card border-0 shadow-sm">
        @csrf
        <div class="card-body">
            @include('demonotes::demo-tags._form')
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.demo-tags.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

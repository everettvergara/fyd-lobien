@extends('admin.layouts.app')

@section('title', 'Create Newsletter List')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Newsletter Lists', 'url' => route('admin.newsletter-lists.index')],
        ['label' => 'Create'],
    ]" />

    <x-admin.page-header title="Create Newsletter List" />

    <form method="POST" action="{{ route('admin.newsletter-lists.store') }}" class="card border-0 shadow-sm">
        @csrf
        <div class="card-body">
            @include('newsletter::newsletter-lists._form')
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.newsletter-lists.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

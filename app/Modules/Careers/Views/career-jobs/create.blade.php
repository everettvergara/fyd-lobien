@extends('admin.layouts.app')

@section('title', 'Create Job Listing')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Job Listings', 'url' => route('admin.career-jobs.index')],
        ['label' => 'Create'],
    ]" />

    <x-admin.page-header title="Create Job Listing" />

    <form method="POST" action="{{ route('admin.career-jobs.store') }}" class="card border-0 shadow-sm">
        @csrf
        <div class="card-body">
            @include('careers::career-jobs._form')
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.career-jobs.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

@extends('admin.layouts.app')

@section('title', 'Edit Job Listing')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Job Listings', 'url' => route('admin.career-jobs.index')],
        ['label' => $job->title],
    ]" />

    <x-admin.page-header title="Edit Job Listing">
        <x-slot:subtitle>{{ $job->slug }}</x-slot:subtitle>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.career-jobs.update', $job) }}" class="card border-0 shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('careers::career-jobs._form', ['job' => $job])
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.career-applications.index', ['job' => $job->id]) }}" class="btn btn-outline-primary">View Applications</a>
            <a href="{{ route('admin.career-jobs.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

@extends('admin.layouts.app')

@section('title', 'Job Listings')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Job Listings'],
    ]" />

    <x-admin.page-header
        title="Job Listings"
        :create-route="route('admin.career-jobs.create')"
        create-label="Add Job"
        :create-model="App\Modules\Careers\Models\CareerJob::class"
    />

    <x-admin.list.index :result="$list" :reset-route="route('admin.career-jobs.index')" />
@endsection

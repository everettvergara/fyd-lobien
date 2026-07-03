@extends('admin.layouts.app')
@section('title', 'Pages')
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Pages']]; @endphp
@section('content')
    <x-admin.page-header
        title="Pages"
        :create-route="route('admin.pages.create')"
        create-label="Add Page"
        :create-model="App\Modules\PageManager\Models\Page::class"
    />

    <div class="mb-3">
        <a href="{{ route('admin.page-master.edit') }}" class="btn btn-outline-secondary btn-sm">
            <i class="{{ admin_icon('bi-layers') }} me-1"></i> Page Master
        </a>
    </div>

    <x-admin.list.index
        :result="$list"
        :reset-route="route('admin.pages.index')"
    />
@endsection

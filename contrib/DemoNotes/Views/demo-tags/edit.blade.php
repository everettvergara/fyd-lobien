@extends('admin.layouts.app')

@section('title', 'Edit Demo Tag')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Demo Tags', 'url' => route('admin.demo-tags.index')],
        ['label' => 'Edit'],
    ]" />

    <x-admin.page-header title="Edit Demo Tag" />

    <form method="POST" action="{{ route('admin.demo-tags.update', $demoTag) }}" class="card border-0 shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('demonotes::demo-tags._form', ['demoTag' => $demoTag])
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.demo-tags.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

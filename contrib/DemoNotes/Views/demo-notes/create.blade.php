@extends('admin.layouts.app')

@section('title', 'Create Demo Note')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Demo Notes', 'url' => route('admin.demo-notes.index')],
        ['label' => 'Create'],
    ]" />

    <x-admin.page-header title="Create Demo Note" />

    <form method="POST" action="{{ route('admin.demo-notes.store') }}" class="card border-0 shadow-sm">
        @csrf
        <div class="card-body">
            @include('demonotes::demo-notes._form')
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.demo-notes.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

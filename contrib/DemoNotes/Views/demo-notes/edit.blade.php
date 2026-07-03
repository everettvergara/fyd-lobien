@extends('admin.layouts.app')

@section('title', 'Edit Demo Note')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Demo Notes', 'url' => route('admin.demo-notes.index')],
        ['label' => 'Edit'],
    ]" />

    <x-admin.page-header title="Edit Demo Note" />

    <form method="POST" action="{{ route('admin.demo-notes.update', $demoNote) }}" class="card border-0 shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('demonotes::demo-notes._form', ['demoNote' => $demoNote])
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.demo-notes.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

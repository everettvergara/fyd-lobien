@extends('admin.layouts.app')

@section('title', 'Create Webform')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Webforms', 'url' => route('admin.webforms.index')],
        ['label' => 'Create'],
    ]" />

    <x-admin.page-header title="Create Webform" />

    <form method="POST" action="{{ route('admin.webforms.store') }}" class="card border-0 shadow-sm">
        @csrf
        <div class="card-body">
            @include('webforms::webforms._form-fields', ['webform' => null])
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save &amp; Open Builder</button>
            <a href="{{ route('admin.webforms.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

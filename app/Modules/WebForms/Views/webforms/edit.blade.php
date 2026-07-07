@extends('admin.layouts.app')

@section('title', 'Edit Webform')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Webforms', 'url' => route('admin.webforms.index')],
        ['label' => $webform->name],
    ]" />

    <x-admin.page-header title="Edit Webform">
        <x-slot:actions>
            <a href="{{ route('admin.webforms.builder', $webform) }}" class="btn btn-outline-primary">
                <i class="bi bi-sliders me-1"></i> Form Builder
            </a>
            <a href="{{ route('admin.webform-submissions.index', ['webform' => $webform->id]) }}" class="btn btn-outline-secondary">
                <i class="bi bi-inbox me-1"></i> Submissions
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.webforms.update', $webform) }}" class="card border-0 shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('webforms::webforms._form-fields', ['webform' => $webform])
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.webforms.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

@extends('admin.layouts.app')

@section('title', 'Edit Newsletter List')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Newsletter Lists', 'url' => route('admin.newsletter-lists.index')],
        ['label' => 'Edit'],
    ]" />

    <x-admin.page-header title="Edit Newsletter List" />

    <form method="POST" action="{{ route('admin.newsletter-lists.update', $newsletterList) }}" class="card border-0 shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('newsletter::newsletter-lists._form', ['newsletterList' => $newsletterList])
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.newsletter-lists.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

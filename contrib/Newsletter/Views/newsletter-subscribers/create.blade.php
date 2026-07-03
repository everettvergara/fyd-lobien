@extends('admin.layouts.app')

@section('title', 'Add Subscriber')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Subscribers', 'url' => route('admin.newsletter-subscribers.index')],
        ['label' => 'Add'],
    ]" />

    <x-admin.page-header title="Add Subscriber" />

    <form method="POST" action="{{ route('admin.newsletter-subscribers.store') }}" class="card border-0 shadow-sm">
        @csrf
        <div class="card-body">
            @include('newsletter::newsletter-subscribers._form')
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.newsletter-subscribers.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

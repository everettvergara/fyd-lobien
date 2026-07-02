@extends('admin.layouts.app')

@section('title', 'Create Banner')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Banners', 'url' => route('admin.banners.index')],
        ['label' => 'Create'],
    ]" />

    <x-admin.page-header title="Create Banner" :back-route="route('admin.banners.index')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.banners.store') }}">
            @csrf
            @include('banners::banners._form', ['banner' => null])
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </x-admin.card>
@endsection

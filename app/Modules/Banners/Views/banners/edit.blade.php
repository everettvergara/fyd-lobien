@extends('admin.layouts.app')

@section('title', 'Edit Banner')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Banners', 'url' => route('admin.banners.index')],
        ['label' => 'Edit'],
    ]" />

    <x-admin.page-header title="Edit Banner" :back-route="route('admin.banners.index')" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.banners.update', $banner) }}">
            @csrf
            @method('PUT')
            @include('banners::banners._form')
        </form>
    </x-admin.card>
@endsection

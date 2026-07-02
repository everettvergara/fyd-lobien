@extends('admin.layouts.app')

@section('title', 'Banners')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Banners'],
    ]" />

    <x-admin.page-header
        title="Banners"
        :create-route="route('admin.banners.create')"
        create-label="Add Banner"
        :create-model="App\Modules\Banners\Models\Banner::class"
    />

    <x-admin.list.index
        :result="$list"
        :bulk-route="route('admin.banners.bulk')"
        :reset-route="route('admin.banners.index')"
    />
@endsection

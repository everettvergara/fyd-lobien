@extends('admin.layouts.app')

@section('title', 'Cities')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Cities'],
    ]" />

    <x-admin.page-header
        title="Cities"
        :create-route="route('admin.cities.create')"
        create-label="Add City"
        :create-model="App\Modules\Address\Models\City::class"
    />

    <x-admin.list.index
        :result="$list"
        :reset-route="route('admin.cities.index')"
    />
@endsection

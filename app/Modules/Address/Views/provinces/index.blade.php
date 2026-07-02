@extends('admin.layouts.app')

@section('title', 'Provinces')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Provinces'],
    ]" />

    <x-admin.page-header
        title="Provinces"
        :create-route="route('admin.provinces.create')"
        create-label="Add Province"
        :create-model="App\Modules\Address\Models\Province::class"
    />

    <x-admin.list.index
        :result="$list"
        :reset-route="route('admin.provinces.index')"
    />
@endsection

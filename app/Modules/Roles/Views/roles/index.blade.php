@extends('admin.layouts.app')

@section('title', 'Roles')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Roles'],
    ]" />

    <x-admin.page-header
        title="Roles"
        :create-route="route('admin.roles.create')"
        create-label="Add Role"
        :create-model="App\Models\Role::class"
    />

    <x-admin.list.index
        :result="$list"
        :reset-route="route('admin.roles.index')"
    />
@endsection

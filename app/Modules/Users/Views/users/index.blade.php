@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Users'],
    ]" />

    <x-admin.page-header
        title="Users"
        :create-route="route('admin.users.create')"
        create-label="Add User"
        :create-model="App\Models\User::class"
    />

    <x-admin.list.index
        :result="$list"
        :bulk-route="route('admin.users.bulk')"
        :reset-route="route('admin.users.index')"
    />
@endsection

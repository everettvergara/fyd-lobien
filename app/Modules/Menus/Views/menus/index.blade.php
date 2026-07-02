@extends('admin.layouts.app')

@section('title', 'Menus')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Menus'],
    ]" />

    <x-admin.page-header
        title="Menus"
        :create-route="route('admin.menus.create')"
        create-label="Add Menu"
        :create-model="App\Modules\Menus\Models\Menu::class"
    />

    <x-admin.list.index
        :result="$list"
        :bulk-route="route('admin.menus.bulk')"
        :reset-route="route('admin.menus.index')"
    />
@endsection

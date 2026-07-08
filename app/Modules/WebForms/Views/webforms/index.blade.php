@extends('admin.layouts.app')

@section('title', 'Webforms')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Webforms'],
    ]" />

    <x-admin.page-header
        title="Webforms"
        :create-route="route('admin.webforms.create')"
        create-label="Add Webform"
        :create-model="App\Modules\WebForms\Models\Webform::class"
    />

    <x-admin.list.index :result="$list" :reset-route="route('admin.webforms.index')" />
@endsection

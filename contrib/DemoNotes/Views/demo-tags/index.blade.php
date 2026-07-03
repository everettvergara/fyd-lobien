@extends('admin.layouts.app')

@section('title', 'Demo Tags')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Demo Tags'],
    ]" />

    <x-admin.page-header
        title="Demo Tags"
        :create-route="route('admin.demo-tags.create')"
        create-label="Add Tag"
        :create-model="App\Modules\DemoNotes\Models\DemoTag::class"
    />

    <x-admin.list.index :result="$list" :reset-route="route('admin.demo-tags.index')" />
@endsection

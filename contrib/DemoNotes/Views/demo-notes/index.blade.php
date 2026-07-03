@extends('admin.layouts.app')

@section('title', 'Demo Notes')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Demo Notes'],
    ]" />

    <x-admin.page-header
        title="Demo Notes"
        :create-route="route('admin.demo-notes.create')"
        create-label="Add Note"
        :create-model="App\Modules\DemoNotes\Models\DemoNote::class"
    />

    <x-admin.list.index :result="$list" :reset-route="route('admin.demo-notes.index')" />
@endsection

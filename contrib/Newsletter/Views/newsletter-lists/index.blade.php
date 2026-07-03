@extends('admin.layouts.app')

@section('title', 'Newsletter Lists')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Newsletter Lists'],
    ]" />

    <x-admin.page-header
        title="Newsletter Lists"
        :create-route="route('admin.newsletter-lists.create')"
        create-label="Add List"
        :create-model="App\Modules\Newsletter\Models\NewsletterList::class"
    />

    <x-admin.list.index :result="$list" :reset-route="route('admin.newsletter-lists.index')" />
@endsection

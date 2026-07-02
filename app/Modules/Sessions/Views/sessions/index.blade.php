@extends('admin.layouts.app')

@section('title', 'Sessions')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Sessions'],
    ]" />

    <x-admin.page-header title="Active Sessions" />

    <x-admin.list.index
        :result="$list"
        :reset-route="route('admin.sessions.index')"
    />
@endsection

@extends('admin.layouts.app')

@section('title', 'Audit Logs')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Audit Logs'],
    ]" />

    <x-admin.page-header title="Audit Logs" />

    <x-admin.list.index
        :result="$list"
        :reset-route="route('admin.audit-logs.index')"
    />
@endsection

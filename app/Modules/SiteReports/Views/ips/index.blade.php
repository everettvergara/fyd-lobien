@extends('admin.layouts.app')

@section('title', 'Hits by IP Address')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Hits by IP Address'],
    ]" />

    <x-admin.page-header title="Hits by IP Address" />

    <x-admin.list.index
        :result="$list"
        :reset-route="route('admin.site-reports.ips.index')"
    />
@endsection

@extends('admin.layouts.app')

@section('title', 'Most Visited Pages')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Most Visited Pages'],
    ]" />

    <x-admin.page-header title="Most Visited Pages" />

    <x-admin.list.index
        :result="$list"
        :reset-route="route('admin.site-reports.pages.index')"
    />
@endsection

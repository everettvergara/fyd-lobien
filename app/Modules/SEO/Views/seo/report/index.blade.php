@extends('admin.layouts.app')

@section('title', 'SEO Report')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'SEO Report'],
    ]" />

    <x-admin.page-header title="SEO Report" />

    <x-admin.list.index
        :result="$list"
        :reset-route="route('admin.seo.report.index')"
    />
@endsection

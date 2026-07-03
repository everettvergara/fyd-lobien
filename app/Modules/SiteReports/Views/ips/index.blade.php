@extends('admin.layouts.app')

@section('title', 'Hits by IP Address')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Hits by IP Address'],
    ]" />

    <x-admin.page-header title="Hits by IP Address" />

    @if (! $canBlockIp)
        <div class="alert alert-info small mb-3" role="status">
            You can view this report, but blocking IP addresses requires the <strong>Block IP Addresses</strong> permission.
            Use the shield icon in the Actions column when that permission is assigned to your role.
        </div>
    @else
        <p class="text-muted small mb-3">
            Use the shield icon in the Actions column to block or unblock an IP address from the public site.
        </p>
    @endif

    <x-admin.list.index
        :result="$list"
        :reset-route="route('admin.site-reports.ips.index')"
    />
@endsection

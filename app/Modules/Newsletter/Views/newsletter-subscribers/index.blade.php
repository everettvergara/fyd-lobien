@extends('admin.layouts.app')

@section('title', 'Subscribers')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Subscribers'],
    ]" />

    <x-admin.page-header
        title="Subscribers"
        :create-route="route('admin.newsletter-subscribers.create')"
        create-label="Add Subscriber"
        :create-model="App\Modules\Newsletter\Models\NewsletterSubscriber::class"
    >
        <x-slot:actions>
            @can('export', App\Modules\Newsletter\Models\NewsletterSubscriber::class)
                <a href="{{ route('admin.newsletter-subscribers.export', request()->query()) }}" class="btn btn-outline-secondary">
                    <i class="{{ admin_icon('bi-download') }} me-1"></i> Download CSV
                </a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.list.index
        :result="$list"
        :bulk-route="url(config('fyd.admin.prefix').'/newsletter-subscribers/bulk')"
        :reset-route="route('admin.newsletter-subscribers.index')"
    />
@endsection

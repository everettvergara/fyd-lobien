@extends('admin.layouts.app')

@section('title', 'Send History')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Send History'],
    ]" />

    <x-admin.page-header title="Send History">
        <x-slot:actions>
            @can('send', App\Modules\Newsletter\Models\NewsletterSend::class)
                <a href="{{ route('admin.newsletters.compose') }}" class="btn btn-primary">
                    <i class="{{ admin_icon('bi-send') }} me-1"></i> Compose Newsletter
                </a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.list.index :result="$list" :reset-route="route('admin.newsletter-sends.index')" />
@endsection

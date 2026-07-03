@extends('admin.layouts.app')

@section('title', 'Applications')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Applications'],
    ]" />

    <x-admin.page-header title="Applications" />

    <x-admin.list.index
        :result="$list"
        :bulk-route="url(config('fyd.admin.prefix').'/career-applications/bulk-delete')"
        :reset-route="request()->fullUrlWithQuery(['search' => null, 'job' => request('job')])"
    />

    @if (request('job'))
        @push('scripts')
            <script>
                document.getElementById('career-applications-bulk-form')?.insertAdjacentHTML(
                    'beforeend',
                    '<input type="hidden" name="job" value="{{ request('job') }}">'
                );
            </script>
        @endpush
    @endif
@endsection

@extends('admin.layouts.app')

@section('title', 'Submissions')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Submissions'],
    ]" />

    <x-admin.page-header title="Submissions" />

    <x-admin.list.index
        :result="$list"
        :bulk-route="url(config('fyd.admin.prefix').'/webform-submissions/bulk-delete')"
        :reset-route="request()->fullUrlWithQuery(['search' => null, 'webform' => request('webform')])"
    />

    @if (request('webform'))
        @push('scripts')
            <script>
                document.getElementById('webform-submissions-bulk-form')?.insertAdjacentHTML(
                    'beforeend',
                    '<input type="hidden" name="webform" value="{{ request('webform') }}">'
                );
            </script>
        @endpush
    @endif
@endsection

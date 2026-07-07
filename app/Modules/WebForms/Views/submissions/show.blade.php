@extends('admin.layouts.app')

@section('title', 'Submission #'.$submission->id)

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Submissions', 'url' => route('admin.webform-submissions.index', array_filter(['webform' => $webformFilter]))],
        ['label' => '#'.$submission->id],
    ]" />

    <x-admin.page-header title="Submission #{{ $submission->id }}">
        <x-slot:subtitle>
            @if ($submission->webform)
                <a href="{{ route('admin.webform-submissions.index', ['webform' => $submission->webform_id]) }}" class="text-decoration-none">
                    {{ $submission->webform->name }}
                </a>
                · {{ $submission->created_at->format('Y-m-d H:i:s') }}
            @endif
        </x-slot:subtitle>
    </x-admin.page-header>

    <div class="row g-4">
        <div class="col-lg-8">
            <x-admin.card title="Submitted values">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach ($submission->data ?? [] as $key => $value)
                                <tr>
                                    <th class="text-muted w-25">{{ $submission->webform?->fieldLabel($key) ?? $key }}</th>
                                    <td>
                                        @if (is_bool($value))
                                            {{ $value ? 'Yes' : 'No' }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        </div>

        <div class="col-lg-4">
            <x-admin.card title="Metadata">
                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted">Form</dt>
                    <dd class="col-sm-8">{{ $submission->webform?->name ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Submitted</dt>
                    <dd class="col-sm-8">{{ $submission->created_at->format('Y-m-d H:i:s') }}</dd>
                    <dt class="col-sm-4 text-muted">IP address</dt>
                    <dd class="col-sm-8">{{ $submission->ip_address ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">User agent</dt>
                    <dd class="col-sm-8 text-break">{{ $submission->user_agent ?? '—' }}</dd>
                </dl>
            </x-admin.card>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('admin.webform-submissions.index', array_filter(['webform' => $webformFilter ?? $submission->webform_id])) }}" class="btn btn-outline-secondary">
                    Back to list
                </a>
                <form method="POST" action="{{ route('admin.webform-submissions.destroy', $submission) }}" onsubmit="return confirm('Delete this submission?')">
                    @csrf
                    @method('DELETE')
                    @if ($webformFilter)
                        <input type="hidden" name="webform" value="{{ $webformFilter }}">
                    @endif
                    @can('delete', $submission)
                        <button type="submit" class="btn btn-outline-danger">Delete</button>
                    @endcan
                </form>
            </div>
        </div>
    </div>
@endsection

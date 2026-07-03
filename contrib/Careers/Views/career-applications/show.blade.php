@extends('admin.layouts.app')

@section('title', 'Application #'.$application->id)

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Applications', 'url' => route('admin.career-applications.index', array_filter(['job' => $jobFilter ?? $application->career_job_id]))],
        ['label' => '#'.$application->id],
    ]" />

    <x-admin.page-header title="Application #{{ $application->id }}">
        <x-slot:subtitle>
            @if ($application->job)
                <a href="{{ route('admin.career-applications.index', ['job' => $application->career_job_id]) }}" class="text-decoration-none">
                    {{ $application->job->title }}
                </a>
                · {{ $application->created_at->format('Y-m-d H:i:s') }}
            @endif
        </x-slot:subtitle>
    </x-admin.page-header>

    <div class="row g-4">
        <div class="col-lg-8">
            <x-admin.card title="Applicant details">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Name</dt>
                    <dd class="col-sm-9">{{ $application->name }}</dd>
                    <dt class="col-sm-3 text-muted">Email</dt>
                    <dd class="col-sm-9"><a href="mailto:{{ $application->email }}">{{ $application->email }}</a></dd>
                    <dt class="col-sm-3 text-muted">Contact Number</dt>
                    <dd class="col-sm-9">{{ $application->contact_number }}</dd>
                    <dt class="col-sm-3 text-muted">Remarks</dt>
                    <dd class="col-sm-9">{{ $application->remarks ?: '—' }}</dd>
                </dl>
            </x-admin.card>
        </div>

        <div class="col-lg-4">
            <x-admin.card title="Metadata">
                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted">Job</dt>
                    <dd class="col-sm-8">{{ $application->job?->title ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Submitted</dt>
                    <dd class="col-sm-8">{{ $application->created_at->format('Y-m-d H:i:s') }}</dd>
                    <dt class="col-sm-4 text-muted">IP address</dt>
                    <dd class="col-sm-8">{{ $application->ip_address ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">User agent</dt>
                    <dd class="col-sm-8 text-break">{{ $application->user_agent ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Resume</dt>
                    <dd class="col-sm-8">
                        <a href="{{ route('admin.career-applications.download-resume', $application) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download"></i> Download PDF
                        </a>
                    </dd>
                </dl>
            </x-admin.card>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('admin.career-applications.index', array_filter(['job' => $jobFilter ?? $application->career_job_id])) }}" class="btn btn-outline-secondary">
                    Back to list
                </a>
                <form method="POST" action="{{ route('admin.career-applications.destroy', $application) }}" onsubmit="return confirm('Delete this application?')">
                    @csrf
                    @method('DELETE')
                    @if ($jobFilter ?? $application->career_job_id)
                        <input type="hidden" name="job" value="{{ $jobFilter ?? $application->career_job_id }}">
                    @endif
                    @can('delete', $application)
                        <button type="submit" class="btn btn-outline-danger">Delete</button>
                    @endcan
                </form>
            </div>
        </div>
    </div>
@endsection

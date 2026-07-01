@extends('admin.layouts.app')
@section('title', 'Audit Logs')
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Audit Logs']]; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Audit Logs</h1>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Module</label>
                <select name="module" class="form-select">
                    <option value="">All modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ $module }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Action</label>
                <select name="action" class="form-select">
                    <option value="">All actions</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">User ID</label>
                <input type="number" name="user_id" class="form-control" value="{{ $filters['user_id'] ?? '' }}" placeholder="Optional">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary btn-sm me-2">Filter</button>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>When</th>
                    <th>User</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="text-muted small">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                        <td class="small">{{ $log->user?->name ?? 'System' }}</td>
                        <td><span class="badge bg-secondary-subtle text-secondary">{{ $log->module }}</span></td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $log->action }}</span></td>
                        <td class="small">{{ $log->description() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No audit log entries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())
        <div class="card-footer bg-white">{{ $logs->links() }}</div>
    @endif
</div>
@endsection

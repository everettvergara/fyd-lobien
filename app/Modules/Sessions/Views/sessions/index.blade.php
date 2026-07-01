@extends('admin.layouts.app')
@section('title', 'Sessions')
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Sessions']]; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Active Sessions</h1>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">User ID</label>
                <input type="number" name="user_id" class="form-control" value="{{ $filters['user_id'] ?? '' }}" placeholder="Filter by user ID">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary btn-sm me-2">Filter</button>
                <a href="{{ route('admin.sessions.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                    <th>Last Activity</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sessions as $session)
                    <tr>
                        <td class="small">
                            @if ($session->user)
                                {{ $session->user->name }}<br>
                                <span class="text-muted">{{ $session->user->email }}</span>
                            @else
                                <span class="text-muted">Guest</span>
                            @endif
                        </td>
                        <td class="small">{{ $session->ip_address ?? '—' }}</td>
                        <td class="small text-truncate" style="max-width: 240px;" title="{{ $session->user_agent }}">{{ $session->user_agent ?? '—' }}</td>
                        <td class="text-muted small">{{ $session->lastActiveAt()->diffForHumans() }}</td>
                        <td class="text-end">
                            @can('delete', $session)
                                <form method="POST" action="{{ route('admin.sessions.destroy', $session) }}" class="d-inline" onsubmit="return confirm('Revoke this session?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Revoke</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No active sessions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($sessions->hasPages())
        <div class="card-footer bg-white">{{ $sessions->links() }}</div>
    @endif
</div>
@endsection

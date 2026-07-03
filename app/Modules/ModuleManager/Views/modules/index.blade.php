@extends('admin.layouts.app')

@section('title', 'Modules')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Modules'],
    ]" />

    <x-admin.page-header title="Modules" />

    @if ($modules->isEmpty())
        <div class="alert alert-info">
            Copy a module from <code>contrib/{Name}/</code> to <code>app/Modules/{Name}/</code>, then refresh this page.
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Group</th>
                            <th>Features</th>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Requires</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($modules as $module)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $module['name'] }}</div>
                                    @if ($module['description'])
                                        <div class="small text-muted">{{ $module['description'] }}</div>
                                    @endif
                                </td>
                                <td>{{ $module['group'] }}</td>
                                <td>
                                    @if (!empty($module['features']))
                                        <ul class="small mb-0 ps-3">
                                            @foreach ($module['features'] as $feature)
                                                <li>{{ $feature['label'] ?? 'Feature' }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="small">{{ $module['version'] }}</td>
                                <td>
                                    @php
                                        $badge = match ($module['status']) {
                                            'installed' => 'success',
                                            'disabled' => 'warning',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }} text-capitalize">{{ $module['status'] }}</span>
                                    @if (! $module['compatible'])
                                        <div class="small text-danger mt-1">Incompatible CMS version</div>
                                    @endif
                                </td>
                                <td class="small">
                                    @if (!empty($module['requires_core']))
                                        {{ implode(', ', $module['requires_core']) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex flex-wrap gap-1 justify-content-end">
                                        @if ($module['status'] === 'available')
                                            @can('install', App\Framework\ModuleManager::class)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#install-modal-{{ $module['slug'] }}"
                                                    @disabled(! $module['compatible'])
                                                >
                                                    Install
                                                </button>
                                            @endcan
                                        @elseif ($module['status'] === 'installed')
                                            @can('disable', App\Framework\ModuleManager::class)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-warning"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#disable-modal-{{ $module['slug'] }}"
                                                >
                                                    Disable
                                                </button>
                                            @endcan
                                            @can('uninstall', App\Framework\ModuleManager::class)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#uninstall-modal-{{ $module['slug'] }}"
                                                >
                                                    Uninstall
                                                </button>
                                            @endcan
                                        @elseif ($module['status'] === 'disabled')
                                            @can('enable', App\Framework\ModuleManager::class)
                                                <form method="POST" action="{{ route('admin.modules.enable', $module['name']) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">Enable</button>
                                                </form>
                                            @endcan
                                            @can('uninstall', App\Framework\ModuleManager::class)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#uninstall-modal-{{ $module['slug'] }}"
                                                >
                                                    Uninstall
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @foreach ($modules as $module)
            @if ($module['status'] === 'available')
                @include('modulemanager::modules.partials.install-modal', ['module' => $module])
            @endif
            @if (in_array($module['status'], ['installed', 'disabled'], true))
                @include('modulemanager::modules.partials.disable-modal', ['module' => $module])
                @include('modulemanager::modules.partials.uninstall-modal', ['module' => $module])
            @endif
        @endforeach
    @endif
@endsection

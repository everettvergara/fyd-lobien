@extends('admin.layouts.app')

@section('title', 'Public Themes')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Public Themes'],
    ]" />

    <x-admin.page-header title="Public Themes" />

    <p class="text-muted mb-4">
        Manage installable public website themes. Author themes under <code>contrib_themes/</code>,
        install them into <code>themes/</code>, run <code>npm run build</code>, then activate one below.
        Admin portal styling is not affected.
    </p>

    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="installed-tab" data-bs-toggle="tab" data-bs-target="#installed-panel" type="button" role="tab">
                Installed ({{ $installed->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="available-tab" data-bs-toggle="tab" data-bs-target="#available-panel" type="button" role="tab">
                Available ({{ $available->count() }})
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="installed-panel" role="tabpanel">
            @if ($installed->isEmpty())
                <div class="alert alert-warning">
                    No themes are installed under <code>themes/</code>. Install <code>fyd-default</code> from the Available tab or copy a theme folder manually.
                </div>
            @else
                @can('update', App\Modules\Themes\Models\ThemeSettings::class)
                    <form method="POST" action="{{ route('admin.themes.update') }}" class="mb-4">
                        @csrf
                        @method('PUT')

                        <div class="card border-0 shadow-sm">
                            <div class="card-header">Active Public Theme</div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach ($installed as $theme)
                                        <div class="col-md-6 col-lg-4">
                                            <label class="card h-100 border {{ $activeTheme === $theme['slug'] ? 'border-primary' : '' }} {{ ! ($theme['valid'] ?? false) ? 'opacity-50' : '' }}">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input
                                                            class="form-check-input"
                                                            type="radio"
                                                            name="active_theme"
                                                            id="theme-{{ $theme['slug'] }}"
                                                            value="{{ $theme['slug'] }}"
                                                            @checked($activeTheme === $theme['slug'])
                                                            @disabled(! ($theme['valid'] ?? false))
                                                        >
                                                        <span class="form-check-label w-100">
                                                            <span class="fw-medium d-block">{{ $theme['name'] }}</span>
                                                            <span class="small text-muted">{{ $theme['slug'] }} · v{{ $theme['version'] }}</span>
                                                            @if ($activeTheme === $theme['slug'])
                                                                <span class="badge bg-primary-subtle text-primary mt-2">Active</span>
                                                            @endif
                                                            @if ($theme['protected'] ?? false)
                                                                <span class="badge bg-secondary-subtle text-secondary mt-2">Protected</span>
                                                            @endif
                                                            @if (! ($theme['valid'] ?? false))
                                                                <span class="badge bg-danger-subtle text-danger mt-2">Invalid</span>
                                                            @elseif (!empty($theme['warnings']))
                                                                <span class="badge bg-warning-subtle text-warning-emphasis mt-2">Warnings</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    @if (!empty($theme['description']))
                                                        <p class="small text-muted mt-2 mb-0">{{ $theme['description'] }}</p>
                                                    @endif
                                                    @if (!empty($theme['errors']))
                                                        <ul class="small text-danger mt-2 mb-0 ps-3">
                                                            @foreach ($theme['errors'] as $error)
                                                                <li>{{ $error }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                    @if (!empty($theme['warnings']))
                                                        <ul class="small text-warning-emphasis mt-2 mb-0 ps-3">
                                                            @foreach ($theme['warnings'] as $warning)
                                                                <li>{{ $warning }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('active_theme')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Activate Selected Theme</button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="card border-0 shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Version</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($installed as $theme)
                                        <tr>
                                            <td>{{ $theme['name'] }}</td>
                                            <td><code>{{ $theme['slug'] }}</code></td>
                                            <td>{{ $theme['version'] }}</td>
                                            <td>
                                                @if ($activeTheme === $theme['slug'])
                                                    <span class="badge bg-primary-subtle text-primary">Active</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary">Installed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endcan
            @endif
        </div>

        <div class="tab-pane fade" id="available-panel" role="tabpanel">
            @if ($available->isEmpty())
                <div class="alert alert-info">
                    No new themes found in <code>contrib_themes/</code>. Create one with <code>php artisan make:theme {Name}</code>.
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Version</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($available as $theme)
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ $theme['name'] }}</div>
                                            @if (!empty($theme['description']))
                                                <div class="small text-muted">{{ $theme['description'] }}</div>
                                            @endif
                                        </td>
                                        <td><code>{{ $theme['slug'] }}</code></td>
                                        <td>{{ $theme['version'] }}</td>
                                        <td>{{ $theme['author'] ?? '—' }}</td>
                                        <td>
                                            @if (! ($theme['valid'] ?? false))
                                                <span class="badge bg-danger-subtle text-danger">Invalid</span>
                                            @elseif (!empty($theme['warnings']))
                                                <span class="badge bg-warning-subtle text-warning-emphasis">Warnings</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success">Ready</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @can('install', App\Modules\Themes\Models\ThemeSettings::class)
                                                @if ($theme['valid'] ?? false)
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#install-theme-{{ $theme['slug'] }}"
                                                    >
                                                        Install
                                                    </button>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @foreach ($available as $theme)
                    @can('install', App\Modules\Themes\Models\ThemeSettings::class)
                        @if ($theme['valid'] ?? false)
                            <div class="modal fade" id="install-theme-{{ $theme['slug'] }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.themes.install', $theme['slug']) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Install {{ $theme['name'] }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>This copies <code>contrib_themes/{{ $theme['slug'] }}/</code> to <code>themes/{{ $theme['slug'] }}/</code>.</p>
                                                <p class="mb-0">After installing, run <code>npm run build</code> before activating the theme.</p>
                                                @if (!empty($theme['warnings']))
                                                    <div class="alert alert-warning mt-3 mb-0">
                                                        <div class="fw-medium mb-1">Validation warnings</div>
                                                        <ul class="small mb-0 ps-3">
                                                            @foreach ($theme['warnings'] as $warning)
                                                                <li>{{ $warning }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                                <div class="form-check mt-3">
                                                    <input type="checkbox" class="form-check-input" id="confirm-{{ $theme['slug'] }}" name="confirm" value="1" required>
                                                    <label class="form-check-label" for="confirm-{{ $theme['slug'] }}">I understand this will copy theme files to the runtime folder.</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Install Theme</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endcan
                @endforeach
            @endif
        </div>
    </div>
@endsection

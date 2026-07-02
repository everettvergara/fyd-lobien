@extends('admin.layouts.app')

@section('title', 'Pages')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Pages'],
    ]" />

    <x-admin.page-header
        title="Pages"
        :create-route="route('admin.pages.create')"
        create-label="Add Page"
        :create-model="App\Modules\Pages\Models\Page::class"
    />

    <x-admin.card :padding="false">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>URI</th>
                        <th>Status</th>
                        <th>Author</th>
                        <th>Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pages as $page)
                        <tr>
                            <td>
                                <a href="{{ route('admin.pages.show', $page) }}" class="text-decoration-none fw-medium">
                                    {{ $page->title }}
                                </a>
                            </td>
                            <td class="text-muted small">/{{ $page->slug }}</td>
                            <td><x-admin.status-badge :status="$page->status" /></td>
                            <td class="small">{{ $page->author->name }}</td>
                            <td class="text-muted small">{{ $page->updated_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <x-admin.table-actions>
                                    @can('view', $page)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.pages.show', $page) }}">
                                                <i class="bi bi-eye me-2"></i>View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.pages.preview', $page) }}" target="_blank">
                                                <i class="bi bi-box-arrow-up-right me-2"></i>Preview
                                            </a>
                                        </li>
                                    @endcan
                                    @can('update', $page)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.pages.edit', $page) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                    @endcan
                                    @can('publish', $page)
                                        @if ($page->status !== App\Enums\ContentStatus::Published)
                                            <li>
                                                <form method="POST" action="{{ route('admin.pages.publish', $page) }}">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-check-circle me-2"></i>Publish
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    @endcan
                                    @can('update', $page)
                                        @if ($page->status !== App\Enums\ContentStatus::Archived)
                                            <li>
                                                <form method="POST" action="{{ route('admin.pages.archive', $page) }}">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-archive me-2"></i>Archive
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    @endcan
                                    @can('create', App\Modules\Pages\Models\Page::class)
                                        <li>
                                            <form method="POST" action="{{ route('admin.pages.duplicate', $page) }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-copy me-2"></i>Duplicate
                                                </button>
                                            </form>
                                        </li>
                                    @endcan
                                    @can('delete', $page)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Delete this page?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    @endcan
                                </x-admin.table-actions>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty-state :colspan="6" message="No pages found." />
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pages->hasPages())
            <x-slot:footer>{{ $pages->links() }}</x-slot:footer>
        @endif
    </x-admin.card>
@endsection

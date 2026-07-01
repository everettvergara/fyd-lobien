@extends('admin.layouts.app')
@section('title', 'Pages')
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Pages']]; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Pages</h1>
    @can('create', App\Modules\Pages\Models\Page::class)
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> Add Page</a>
    @endcan
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Title</th><th>Slug</th><th>Status</th><th>Author</th><th>Updated</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($pages as $page)
                <tr>
                    <td><a href="{{ route('admin.pages.show', $page) }}" class="text-decoration-none fw-medium">{{ $page->title }}</a></td>
                    <td class="text-muted small">{{ $page->slug }}</td>
                    <td><span class="badge bg-primary-subtle text-primary">{{ $page->status->label() }}</span></td>
                    <td class="small">{{ $page->author->name }}</td>
                    <td class="text-muted small">{{ $page->updated_at->diffForHumans() }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.pages.preview', $page) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-eye"></i></a>
                        @can('update', $page)<a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>@endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No pages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($pages->hasPages())<div class="card-footer bg-white">{{ $pages->links() }}</div>@endif
</div>
@endsection

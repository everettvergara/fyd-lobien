@extends('admin.layouts.app')

@section('title', 'Posts')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Posts'],
    ]" />

    <x-admin.page-header
        title="Posts"
        :create-route="route('admin.posts.create')"
        create-label="Add Post"
        :create-model="App\Modules\Posts\Models\Post::class"
    />

    <x-admin.card :padding="false">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Author</th>
                        <th>Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td class="fw-medium">{{ $post->title }}</td>
                            <td><x-admin.status-badge :status="$post->status" /></td>
                            <td class="small">{{ $post->author->name }}</td>
                            <td class="text-muted small">{{ $post->updated_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <x-admin.table-actions>
                                    @can('update', $post)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.posts.edit', $post) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                    @endcan
                                    @can('publish', $post)
                                        @if ($post->status !== App\Enums\ContentStatus::Published)
                                            <li>
                                                <form method="POST" action="{{ route('admin.posts.publish', $post) }}">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-check-circle me-2"></i>Publish
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    @endcan
                                    @can('create', App\Modules\Posts\Models\Post::class)
                                        <li>
                                            <form method="POST" action="{{ route('admin.posts.duplicate', $post) }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-copy me-2"></i>Duplicate
                                                </button>
                                            </form>
                                        </li>
                                    @endcan
                                    @can('delete', $post)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
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
                        <x-admin.empty-state :colspan="5" message="No posts found." />
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($posts->hasPages())
            <x-slot:footer>{{ $posts->links() }}</x-slot:footer>
        @endif
    </x-admin.card>
@endsection

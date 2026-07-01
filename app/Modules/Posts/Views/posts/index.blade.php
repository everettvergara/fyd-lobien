@extends('admin.layouts.app')
@section('title', 'Posts')
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Posts']]; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Posts</h1>
    @can('create', App\Modules\Posts\Models\Post::class)<a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> Add Post</a>@endcan
</div>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
<thead class="table-light"><tr><th>Title</th><th>Status</th><th>Author</th><th>Updated</th><th></th></tr></thead>
<tbody>@forelse($posts as $post)<tr>
<td class="fw-medium">{{ $post->title }}</td>
<td><span class="badge bg-primary-subtle text-primary">{{ $post->status->label() }}</span></td>
<td class="small">{{ $post->author->name }}</td>
<td class="text-muted small">{{ $post->updated_at->diffForHumans() }}</td>
<td class="text-end">@can('update',$post)<a href="{{ route('admin.posts.edit',$post) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>@endcan</td>
</tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No posts found.</td></tr>@endforelse</tbody>
</table></div>@if($posts->hasPages())<div class="card-footer bg-white">{{ $posts->links() }}</div>@endif</div>
@endsection

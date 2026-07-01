@extends('admin.layouts.app')
@section('title', 'Media Library')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Media Library</h1>
</div>
<div class="row mb-4">
<div class="col-md-8">
<form method="GET" class="d-flex gap-2">
<input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Search files...">
<button class="btn btn-outline-secondary">Search</button>
</form>
</div>
<div class="col-md-4">
@can('create', App\Models\Media::class)
<form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="d-flex gap-2">
@csrf
<input type="file" class="form-control form-control-sm" name="file" required>
<button class="btn btn-primary btn-sm">Upload</button>
</form>
@endcan
</div>
</div>
<div class="row g-3">
@forelse($media as $item)
<div class="col-md-3 col-sm-4 col-6">
<div class="card">
@if(str_starts_with($item->mime_type,'image/'))
<img src="{{ $item->url() }}" class="card-img-top" alt="{{ $item->alt_text }}" style="height:120px;object-fit:cover;">
@else
<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:120px;"><i class="bi bi-file-earmark fs-1 text-muted"></i></div>
@endif
<div class="card-body p-2">
<p class="small mb-1 text-truncate" title="{{ $item->original_filename }}">{{ $item->original_filename }}</p>
<p class="text-muted mb-0" style="font-size:0.7rem;">{{ number_format($item->size/1024,1) }} KB</p>
@can('delete',$item)<form method="POST" action="{{ route('admin.media.destroy',$item) }}" class="mt-1">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger w-100">Delete</button></form>@endcan
</div></div></div>
@empty
<div class="col-12 text-center text-muted py-5">No media files found. Upload your first file above.</div>
@endforelse
</div>
@if($media->hasPages())<div class="mt-3">{{ $media->links() }}</div>@endif
@endsection

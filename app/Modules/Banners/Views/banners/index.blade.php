@extends('admin.layouts.app')
@section('title', 'Banners')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Banners</h1>
    @can('create', App\Modules\Banners\Models\Banner::class)<a href="{{ route('admin.banners.create') }}" class="btn btn-primary btn-sm">Add Banner</a>@endcan
</div>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
<thead class="table-light"><tr><th>Name</th><th>Type</th><th>Placement</th><th>Status</th><th></th></tr></thead>
<tbody>@forelse($banners as $banner)<tr>
<td class="fw-medium">{{ $banner->name }}</td>
<td class="small">{{ $banner->type->label() }}</td>
<td class="small">{{ $banner->placement->label() }}</td>
<td><span class="badge bg-primary-subtle text-primary">{{ $banner->status->label() }}</span></td>
<td class="text-end">@can('update',$banner)<a href="{{ route('admin.banners.edit',$banner) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>@endcan</td>
</tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No banners found.</td></tr>@endforelse</tbody>
</table></div>@if($banners->hasPages())<div class="card-footer bg-white">{{ $banners->links() }}</div>@endif</div>
@endsection

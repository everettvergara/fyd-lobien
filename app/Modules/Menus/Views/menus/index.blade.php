@extends('admin.layouts.app')
@section('title', 'Menus')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Menus</h1>
    @can('create', App\Modules\Menus\Models\Menu::class)<a href="{{ route('admin.menus.create') }}" class="btn btn-primary btn-sm">Add Menu</a>@endcan
</div>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
<thead class="table-light"><tr><th>Name</th><th>Location</th><th>Items</th><th></th></tr></thead>
<tbody>@forelse($menus as $menu)<tr>
<td class="fw-medium">{{ $menu->name }}</td>
<td><span class="badge bg-secondary-subtle text-secondary">{{ $menu->location->label() }}</span></td>
<td>{{ $menu->all_items_count }}</td>
<td class="text-end">@can('update',$menu)<a href="{{ route('admin.menus.edit',$menu) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>@endcan</td>
</tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">No menus found.</td></tr>@endforelse</tbody>
</table></div></div>
@endsection

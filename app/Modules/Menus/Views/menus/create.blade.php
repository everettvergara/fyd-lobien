@extends('admin.layouts.app')
@section('title', isset($menu) ? 'Edit Menu' : 'Create Menu')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ isset($menu) ? 'Edit Menu' : 'Create Menu' }}</h1>
    <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
</div>
<div class="card"><div class="card-body">
<form method="POST" action="{{ isset($menu) ? route('admin.menus.update', $menu) : route('admin.menus.store') }}">
@csrf @if(isset($menu))@method('PUT')@endif
<div class="row mb-4">
<div class="col-md-6"><label class="form-label">Menu Name</label><input type="text" class="form-control" name="name" value="{{ old('name', $menu?->name) }}" required></div>
<div class="col-md-6"><label class="form-label">Location</label><select class="form-select" name="location">@foreach($locations as $l)<option value="{{ $l->value }}" {{ old('location',$menu?->location?->value)===$l->value?'selected':'' }}>{{ $l->label() }}</option>@endforeach</select></div>
</div>
<h6 class="fw-semibold mb-3">Menu Items</h6>
<div id="menu-items">
@php $items = old('items', $menu?->allItems?->toArray() ?? [['title'=>'','url'=>'','link_type'=>'internal','target'=>'_self']]); @endphp
@foreach($items as $i => $item)
<div class="row mb-2 menu-item-row">
<div class="col-md-3"><input type="text" class="form-control form-control-sm" name="items[{{ $i }}][title]" placeholder="Title" value="{{ $item['title'] ?? '' }}"></div>
<div class="col-md-4"><input type="text" class="form-control form-control-sm" name="items[{{ $i }}][url]" placeholder="URL" value="{{ $item['url'] ?? '' }}"></div>
<div class="col-md-2"><select class="form-select form-select-sm" name="items[{{ $i }}][link_type]"><option value="internal" {{ ($item['link_type']??'')==='internal'?'selected':'' }}>Internal</option><option value="external" {{ ($item['link_type']??'')==='external'?'selected':'' }}>External</option></select></div>
<div class="col-md-2"><select class="form-select form-select-sm" name="items[{{ $i }}][target]"><option value="_self">Same Tab</option><option value="_blank" {{ ($item['target']??'')==='_blank'?'selected':'' }}>New Tab</option></select></div>
<div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-item">&times;</button></div>
</div>
@endforeach
</div>
<button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="add-item">Add Item</button>
<button type="submit" class="btn btn-primary d-block">{{ isset($menu) ? 'Save' : 'Create' }}</button>
</form></div></div>
@push('scripts')<script>
document.getElementById('add-item')?.addEventListener('click',()=>{const c=document.getElementById('menu-items'),i=c.querySelectorAll('.menu-item-row').length;c.insertAdjacentHTML('beforeend',`<div class="row mb-2 menu-item-row"><div class="col-md-3"><input type="text" class="form-control form-control-sm" name="items[${i}][title]" placeholder="Title"></div><div class="col-md-4"><input type="text" class="form-control form-control-sm" name="items[${i}][url]" placeholder="URL"></div><div class="col-md-2"><select class="form-select form-select-sm" name="items[${i}][link_type]"><option value="internal">Internal</option><option value="external">External</option></select></div><div class="col-md-2"><select class="form-select form-select-sm" name="items[${i}][target]"><option value="_self">Same Tab</option><option value="_blank">New Tab</option></select></div><div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-item">&times;</button></div></div>`);});
document.getElementById('menu-items')?.addEventListener('click',e=>{if(e.target.classList.contains('remove-item'))e.target.closest('.menu-item-row').remove();});
</script>@endpush
@endsection

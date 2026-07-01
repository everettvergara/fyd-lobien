@extends('admin.layouts.app')
@section('title', isset($banner) ? 'Edit Banner' : 'Create Banner')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ isset($banner) ? 'Edit Banner' : 'Create Banner' }}</h1>
    <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
</div>
<div class="card"><div class="card-body">
<form method="POST" action="{{ isset($banner) ? route('admin.banners.update', $banner) : route('admin.banners.store') }}">
@csrf @if(isset($banner))@method('PUT')@endif
<div class="row">
<div class="col-md-6 mb-3"><label class="form-label">Banner Name</label><input type="text" class="form-control" name="name" value="{{ old('name', $banner?->name) }}" required></div>
<div class="col-md-6 mb-3"><label class="form-label">Title</label><input type="text" class="form-control" name="title" value="{{ old('title', $banner?->title) }}"></div>
<div class="col-md-6 mb-3"><label class="form-label">Type</label><select class="form-select" name="type">@foreach($types as $t)<option value="{{ $t->value }}" {{ old('type',$banner?->type?->value)===$t->value?'selected':'' }}>{{ $t->label() }}</option>@endforeach</select></div>
<div class="col-md-6 mb-3"><label class="form-label">Placement</label><select class="form-select" name="placement">@foreach($placements as $p)<option value="{{ $p->value }}" {{ old('placement',$banner?->placement?->value)===$p->value?'selected':'' }}>{{ $p->label() }}</option>@endforeach</select></div>
<div class="col-12 mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2">{{ old('description', $banner?->description) }}</textarea></div>
<div class="col-md-4 mb-3">@include('media::partials.media-picker', ['name' => 'desktop_image_id', 'label' => 'Desktop Image', 'value' => $banner?->desktop_image_id, 'previewUrl' => $banner?->desktopImage?->url()])</div>
<div class="col-md-4 mb-3">@include('media::partials.media-picker', ['name' => 'mobile_image_id', 'label' => 'Mobile Image', 'value' => $banner?->mobile_image_id, 'previewUrl' => $banner?->mobileImage?->url()])</div>
<div class="col-md-4 mb-3">@include('media::partials.media-picker', ['name' => 'background_image_id', 'label' => 'Background Image', 'value' => $banner?->background_image_id, 'previewUrl' => $banner?->backgroundImage?->url()])</div>
<div class="col-md-4 mb-3"><label class="form-label">Button Text</label><input type="text" class="form-control" name="button_text" value="{{ old('button_text', $banner?->button_text) }}"></div>
<div class="col-md-4 mb-3"><label class="form-label">Button URL</label><input type="text" class="form-control" name="button_url" value="{{ old('button_url', $banner?->button_url) }}"></div>
<div class="col-md-4 mb-3"><label class="form-label">Status</label><select class="form-select" name="status">@foreach($statuses as $s)<option value="{{ $s->value }}" {{ old('status',$banner?->status?->value)===$s->value?'selected':'' }}>{{ $s->label() }}</option>@endforeach</select></div>
</div>
<button type="submit" class="btn btn-primary">{{ isset($banner) ? 'Save' : 'Create' }}</button>
</form></div></div>
@endsection

@extends('admin.layouts.app')
@section('title', 'Page Master')
@php
    $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Page Master']];
    $initialBlocks = old('blocks', $pageMaster->blocks->map(fn ($block) => [
        'region_key' => $block->region_key,
        'block_type' => $block->block_type,
        'sort_order' => $block->sort_order,
        'config' => $block->config ?? [],
    ])->values()->all());
@endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Page Master</h1>
        <p class="text-muted mb-0">Default blocks and SEO fallbacks inherited by all pages unless overridden.</p>
    </div>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">Back to Pages</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.page-master.update') }}">@csrf @method('PUT')
            <div class="row g-4">
                <div class="col-lg-8">
                    @include('pagemanager::partials.block-editor', [
                        'regions' => $regions,
                        'blockPalette' => $blockPalette,
                        'initialBlocks' => $initialBlocks,
                        'inputPrefix' => 'blocks',
                    ])
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label">Default SEO Title Suffix</label>
                        <input type="text" class="form-control" name="default_seo_title_suffix" value="{{ old('default_seo_title_suffix', $pageMaster->default_seo_title_suffix) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Default Robots</label>
                        <input type="text" class="form-control" name="default_robots" value="{{ old('default_robots', $pageMaster->default_robots) }}">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Save Page Master</button>
        </form>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Edit Content Block')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Content Blocks', 'url' => route('admin.content-blocks.index')],
        ['label' => 'Edit'],
    ]" />

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Content Block</h1>
        <a href="{{ route('admin.content-blocks.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    @include('contentblocks::content-blocks._form', ['contentBlock' => $contentBlock])
@endsection

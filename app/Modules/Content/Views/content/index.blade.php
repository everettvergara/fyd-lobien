@extends('admin.layouts.app')
@section('title', 'Content')
@php
    $typeFilter = request('content_type');
    $filteredType = ($typeFilter && $contentTypes->has($typeFilter)) ? $typeFilter : null;
    $listTitle = $filteredType ? $contentTypes->label($filteredType).'s' : 'Content';
    $createRoute = $filteredType
        ? route('admin.content.create', ['content_type' => $filteredType])
        : route('admin.content.create');
    $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Content']];
@endphp
@section('content')
    <x-admin.page-header
        :title="$listTitle"
        :create-route="$createRoute"
        create-label="Add Content"
        :create-model="App\Modules\Content\Models\Content::class"
    />

    <x-admin.list.index
        :result="$list"
        :bulk-route="route('admin.content.bulk')"
        :reset-route="route('admin.content.index')"
    />
@endsection

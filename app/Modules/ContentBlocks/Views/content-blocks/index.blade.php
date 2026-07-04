@extends('admin.layouts.app')

@section('title', 'Content Blocks')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Content Blocks'],
    ]" />

    <x-admin.page-header
        title="Content Blocks"
        :create-route="route('admin.content-blocks.create')"
        create-label="Add Content Block"
        :create-model="App\Modules\ContentBlocks\Models\ContentBlock::class"
    />

    <x-admin.list.index
        :result="$list"
        :bulk-route="route('admin.content-blocks.bulk')"
        :reset-route="route('admin.content-blocks.index')"
    />
@endsection

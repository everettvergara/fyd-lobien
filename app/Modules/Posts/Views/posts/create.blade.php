@extends('admin.layouts.app')

@section('title', 'Create Post')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Posts', 'url' => route('admin.posts.index')],
        ['label' => 'Create'],
    ]" />

    <x-admin.page-header
        title="Create Post"
        :back-route="route('admin.posts.index')"
    />

    <x-admin.card>
        @include('posts::posts._form', ['statuses' => $statuses])
    </x-admin.card>
@endsection

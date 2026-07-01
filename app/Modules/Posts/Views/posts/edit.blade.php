@extends('admin.layouts.app')

@section('title', 'Edit Post')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Posts', 'url' => route('admin.posts.index')],
        ['label' => 'Edit'],
    ]" />

    <x-admin.page-header
        title="Edit Post"
        :back-route="route('admin.posts.index')"
    />

    <x-admin.card>
        @include('posts::posts._form', ['post' => $post, 'statuses' => $statuses])
    </x-admin.card>
@endsection

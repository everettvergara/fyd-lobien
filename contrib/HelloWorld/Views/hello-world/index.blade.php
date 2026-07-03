@extends('admin.layouts.app')

@section('title', 'Hello World')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Hello World'],
    ]" />

    <x-admin.page-header title="Hello World" />

    <div class="card border-0 shadow-sm">
        <div class="card-body py-5 text-center">
            <p class="display-6 mb-2">Hello, World!</p>
            <p class="text-muted mb-0">
                This page is served by the <strong>HelloWorld</strong> business module in
                <code>app/Modules/HelloWorld</code>.
            </p>
        </div>
    </div>
@endsection

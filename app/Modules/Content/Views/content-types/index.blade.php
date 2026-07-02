@extends('admin.layouts.app')

@section('title', 'Content Types')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Content Types'],
    ]" />

    <x-admin.page-header
        title="Content Types"
        :create-route="route('admin.content-types.create')"
        create-label="Add Content Type"
        :create-model="App\Modules\Content\Models\ContentType::class"
    />

    <p class="text-muted small mb-3">
        Master registry for content classification. Entries created here drive the type dropdown on content forms and list filters.
    </p>

    <x-admin.list.index
        :result="$list"
        :reset-route="route('admin.content-types.index')"
    />
@endsection

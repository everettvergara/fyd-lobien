@php
    $statusOptions = collect($statuses)->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all();
@endphp

<form method="POST" action="{{ isset($post) ? route('admin.posts.update', $post) : route('admin.posts.store') }}">
    @csrf
    @if (isset($post))
        @method('PUT')
    @endif

    <x-admin.form.input label="Title" name="title" :value="isset($post) ? $post->title : null" required />
    <x-admin.form.input label="URL Slug (URI path)" name="slug" :value="isset($post) ? $post->slug : null" required />
    <div class="form-text mb-3">Public URL will be <code>/blog/your-slug</code>. Use lowercase letters, numbers, and hyphens only.</div>
    <x-admin.form.textarea label="Excerpt" name="excerpt" :value="$post?->excerpt" :rows="2" />
    <x-admin.form.textarea label="Content" name="content" :value="$post?->content" :rows="8" />
    <x-admin.form.select
        label="Status"
        name="status"
        :options="$statusOptions"
        :selected="$post?->status?->value"
        required
    />

    <hr>
    @include('seo::partials.seo-fields', ['seo' => isset($post) ? $post->seoMeta : null])

    <button type="submit" class="btn btn-primary mt-3">{{ isset($post) ? 'Save' : 'Create' }}</button>
</form>

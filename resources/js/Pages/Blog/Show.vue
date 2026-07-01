<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

defineProps({
    post: { type: Object, required: true },
});
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="post.seo" />

        <div class="container py-5">
            <Breadcrumb :items="[{ label: 'Blog', url: '/blog' }, { label: post.title }]" />

            <article class="mx-auto" style="max-width: 800px;">
                <header class="mb-4">
                    <h1 class="display-5 fw-bold">{{ post.title }}</h1>
                    <div class="text-muted small">
                        <span v-if="post.author">By {{ post.author }}</span>
                        <span v-if="post.publishedAt" class="ms-2">{{ new Date(post.publishedAt).toLocaleDateString() }}</span>
                    </div>
                </header>

                <img
                    v-if="post.featuredImage"
                    :src="post.featuredImage.url"
                    :alt="post.featuredImage.alt"
                    class="img-fluid rounded mb-4"
                />

                <p v-if="post.excerpt" class="lead text-muted">{{ post.excerpt }}</p>

                <div v-if="post.content" class="content-body" style="white-space: pre-wrap;">{{ post.content }}</div>
            </article>
        </div>
    </PublicLayout>
</template>

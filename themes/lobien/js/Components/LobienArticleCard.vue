<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    content: { type: Object, required: true },
});
</script>

<template>
    <article class="lobien-article-card">
        <div v-if="content.featuredImage" class="lobien-article-image">
            <img
                :src="content.featuredImage.url"
                :alt="content.featuredImage.alt || content.title"
                loading="lazy"
            >
        </div>
        <h3 class="lobien-article-title">{{ content.title }}</h3>
        <p v-if="content.publishedAt" class="lobien-article-meta">{{ content.publishedAt }}</p>
        <p v-if="content.summary" class="lobien-article-summary">{{ content.summary }}</p>
        <Link :href="`/${content.slug}`" class="lobien-article-link">Read more</Link>
    </article>
</template>

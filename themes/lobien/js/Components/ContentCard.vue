<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    content: { type: Object, required: true },
});
</script>

<template>
    <div class="card h-100">
        <img
            v-if="content.featuredImage"
            :src="content.featuredImage.url"
            :alt="content.featuredImage.alt"
            class="card-img-top"
            style="height: 160px; object-fit: cover;"
        />
        <div class="card-body">
            <p v-if="content.contentType" class="text-muted small text-uppercase mb-1">{{ content.contentType.label }}</p>
            <h5 class="card-title">{{ content.title }}</h5>
            <p v-if="content.summary" class="card-text text-muted small">{{ content.summary }}</p>
            <p v-if="content.publishedAt" class="card-text text-muted small mb-2">{{ content.publishedAt }}</p>
            <Link :href="`/${content.slug}`" class="btn btn-sm btn-outline-primary">Read More</Link>
        </div>
    </div>
</template>

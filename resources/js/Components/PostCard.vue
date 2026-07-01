<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    post: { type: Object, required: true },
});
</script>

<template>
    <div class="card h-100">
        <img
            v-if="post.featuredImage"
            :src="post.featuredImage.url"
            :alt="post.featuredImage.alt"
            class="card-img-top"
            style="height: 180px; object-fit: cover;"
        />
        <div v-else class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
            <i class="bi bi-journal-text fs-1 text-muted"></i>
        </div>
        <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ post.title }}</h5>
            <p v-if="post.excerpt" class="card-text text-muted small flex-grow-1">{{ post.excerpt }}</p>
            <div class="d-flex justify-content-between align-items-center mt-auto">
                <small v-if="post.publishedAt" class="text-muted">{{ post.publishedAt }}</small>
                <Link :href="`/blog/${post.slug}`" class="btn btn-sm btn-outline-primary">Read More</Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    page: { type: Object, required: true },
});
</script>

<template>
    <div class="card h-100">
        <img
            v-if="page.featuredImage"
            :src="page.featuredImage.url"
            :alt="page.featuredImage.alt"
            class="card-img-top"
            style="height: 160px; object-fit: cover;"
        />
        <div class="card-body">
            <h5 class="card-title">{{ page.title }}</h5>
            <p v-if="page.summary" class="card-text text-muted small">{{ page.summary }}</p>
            <Link :href="`/${page.slug}`" class="btn btn-sm btn-outline-primary">View Page</Link>
        </div>
    </div>
</template>

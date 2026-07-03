<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    heading: { type: String, default: 'Featured Content' },
    items: { type: Array, default: () => [] },
});
</script>

<template>
    <section v-if="items.length" class="public-section">
        <div class="container">
            <h2 class="fw-bold text-center mb-5">{{ heading }}</h2>
            <div class="row g-4">
                <div v-for="(item, index) in items" :key="index" class="col-md-4">
                    <div class="card h-100">
                        <img
                            v-if="item.featuredImage"
                            :src="item.featuredImage.url"
                            :alt="item.featuredImage.alt"
                            class="card-img-top"
                            style="height: 160px; object-fit: cover;"
                        >
                        <div class="card-body">
                            <h5 class="card-title">{{ item.title }}</h5>
                            <p v-if="item.summary" class="card-text text-muted small">{{ item.summary }}</p>
                            <Link :href="`/${item.slug}`" class="btn btn-sm btn-outline-primary">Read More</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

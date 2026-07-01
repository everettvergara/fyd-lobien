<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PostCard from '@/Components/PostCard.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    posts: { type: Object, required: true },
    seo: { type: Object, default: () => ({}) },
});
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="seo" />

        <div class="container py-5">
            <Breadcrumb :items="[{ label: 'Blog' }]" />
            <h1 class="display-5 fw-bold mb-5">Blog</h1>

            <div v-if="posts.data?.length" class="row g-4">
                <div v-for="(post, i) in posts.data" :key="i" class="col-md-4">
                    <PostCard :post="post" />
                </div>
            </div>
            <p v-else class="text-muted text-center py-5">No posts published yet.</p>

            <nav v-if="posts.links?.length > 3" class="mt-4 d-flex justify-content-center">
                <ul class="pagination">
                    <li v-for="(link, i) in posts.links" :key="i" class="page-item" :class="{ active: link.active, disabled: !link.url }">
                        <Link v-if="link.url" :href="link.url" class="page-link" v-html="link.label" />
                        <span v-else class="page-link" v-html="link.label" />
                    </li>
                </ul>
            </nav>
        </div>
    </PublicLayout>
</template>

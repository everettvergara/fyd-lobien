<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    query: { type: String, default: '' },
    results: { type: Array, default: () => [] },
    seo: { type: Object, default: () => ({}) },
});

const searchQuery = ref(props.query);

function submitSearch() {
    router.get('/search', { q: searchQuery.value }, { preserveState: true });
}
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="seo" />

        <div class="container py-5">
            <Breadcrumb :items="[{ label: 'Search' }]" />
            <h1 class="display-5 fw-bold mb-4">Search</h1>

            <form class="mb-5" @submit.prevent="submitSearch">
                <div class="input-group input-group-lg">
                    <input v-model="searchQuery" type="text" class="form-control" placeholder="Search pages and posts..." />
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>

            <div v-if="query && results.length">
                <p class="text-muted mb-4">{{ results.length }} result(s) for "{{ query }}"</p>
                <div class="list-group">
                    <Link
                        v-for="(result, i) in results"
                        :key="i"
                        :href="result.type === 'post' ? `/blog/${result.slug}` : `/${result.slug}`"
                        class="list-group-item list-group-item-action"
                    >
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ result.title }}</h6>
                                <p v-if="result.excerpt || result.summary" class="mb-0 small text-muted">
                                    {{ result.excerpt || result.summary }}
                                </p>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary">{{ result.type }}</span>
                        </div>
                    </Link>
                </div>
            </div>
            <p v-else-if="query" class="text-muted">No results found for "{{ query }}".</p>
        </div>
    </PublicLayout>
</template>

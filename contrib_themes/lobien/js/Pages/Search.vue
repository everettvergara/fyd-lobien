<script setup>
import PublicLayout from '../Layouts/PublicLayout.vue';
import SeoHead from '../Components/SeoHead.vue';
import Breadcrumb from '../Components/Breadcrumb.vue';
import { useRecaptcha } from '../composables/useRecaptcha';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    query: { type: String, default: '' },
    results: { type: Array, default: () => [] },
    seo: { type: Object, default: () => ({}) },
});

const page = usePage();
const { execute } = useRecaptcha();

const searchQuery = ref(props.query);
const submitting = ref(false);

const recaptchaError = computed(() => page.props.errors?.recaptcha_token);

async function submitSearch() {
    if (submitting.value) {
        return;
    }

    submitting.value = true;

    try {
        const recaptchaToken = await execute('search');

        router.post('/search', {
            q: searchQuery.value,
            recaptcha_token: recaptchaToken,
        }, {
            preserveState: true,
            onFinish: () => {
                submitting.value = false;
            },
        });
    } catch {
        submitting.value = false;
    }
}
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="seo" />

        <div class="lobien-container py-5">
            <Breadcrumb :items="[{ label: 'Search' }]" />
            <h1 class="fw-normal text-uppercase mb-4">Search</h1>

            <form class="mb-5" @submit.prevent="submitSearch">
                <div class="input-group input-group-lg">
                    <input v-model="searchQuery" type="text" class="form-control" placeholder="Search content...">
                    <button class="btn btn-primary" type="submit" :disabled="submitting">
                        {{ submitting ? 'Searching...' : 'Search' }}
                    </button>
                </div>
                <div v-if="recaptchaError" class="text-danger small mt-2">
                    {{ recaptchaError }}
                </div>
            </form>

            <div v-if="query && results.length">
                <p class="text-muted mb-4">{{ results.length }} result(s) for "{{ query }}"</p>
                <div class="list-group">
                    <Link
                        v-for="(result, i) in results"
                        :key="i"
                        :href="`/${result.slug}`"
                        class="list-group-item list-group-item-action"
                    >
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-uppercase">{{ result.title }}</h6>
                                <p v-if="result.summary" class="mb-0 small text-muted">
                                    {{ result.summary }}
                                </p>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary">{{ result.typeLabel }}</span>
                        </div>
                    </Link>
                </div>
            </div>
            <p v-else-if="query" class="text-muted">No results found for "{{ query }}".</p>
        </div>
    </PublicLayout>
</template>

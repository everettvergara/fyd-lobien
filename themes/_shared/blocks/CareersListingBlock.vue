<script setup>
import PageContentShell from '../Components/PageContentShell.vue';
import { onMounted, ref } from 'vue';

const loading = ref(true);
const loadError = ref('');
const jobs = ref([]);

async function loadJobs() {
    loading.value = true;
    loadError.value = '';

    try {
        const response = await fetch('/api/careers/jobs', { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Unable to load job listings.');
        const payload = await response.json();
        jobs.value = payload.jobs ?? [];
    } catch (error) {
        loadError.value = error instanceof Error ? error.message : 'Unable to load job listings.';
    } finally {
        loading.value = false;
    }
}

onMounted(loadJobs);
</script>

<template>
    <PageContentShell spacing="section">
        <section class="careers-listing">
            <h2 class="h3 fw-bold mb-4">Open Positions</h2>
            <div v-if="loading" class="text-muted">Loading job listings...</div>
            <div v-else-if="loadError" class="alert alert-danger">{{ loadError }}</div>
            <div v-else-if="jobs.length === 0" class="text-muted">No open positions at this time.</div>
            <div v-else class="row g-4">
                <div v-for="job in jobs" :key="job.id" class="col-md-6">
                    <article class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h3 class="h5 card-title mb-2">
                                <a :href="job.url" class="text-decoration-none">{{ job.title }}</a>
                            </h3>
                            <p v-if="job.summary" class="card-text text-muted small mb-0">{{ job.summary }}</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </PageContentShell>
</template>

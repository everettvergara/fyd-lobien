<script setup>
import { onMounted, ref } from 'vue';

const loading = ref(true);
const loadError = ref('');
const jobs = ref([]);

async function loadJobs() {
    loading.value = true;
    loadError.value = '';

    try {
        const response = await fetch('/api/careers/jobs', {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Unable to load job listings.');
        }

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
    <section class="careers-listing lobien-section">
        <div class="lobien-section-heading">
            <h2>Open Positions</h2>
        </div>

        <div v-if="loading" class="text-muted">Loading job listings...</div>
        <div v-else-if="loadError" class="alert alert-danger">{{ loadError }}</div>
        <div v-else-if="jobs.length === 0" class="text-muted">No open positions at this time.</div>

        <div v-else class="row g-4">
            <div v-for="job in jobs" :key="job.id" class="col-md-6 col-lg-4">
                <article class="careers-job card h-100 border-0 shadow-sm">
                    <img
                        v-if="job.picture"
                        :src="job.picture.url"
                        :alt="job.picture.alt"
                        class="careers-job__picture card-img-top"
                        style="height: 180px; object-fit: cover;"
                    >
                    <div class="card-body d-flex flex-column">
                        <h3 class="careers-job__title card-title mb-2">
                            <a :href="job.url" class="text-decoration-none stretched-link">{{ job.title }}</a>
                        </h3>
                        <p v-if="job.summary" class="card-text text-muted small mb-3">{{ job.summary }}</p>
                        <ul class="list-unstyled small text-muted mt-auto mb-0">
                            <li v-if="job.department"><strong>Department:</strong> {{ job.department }}</li>
                            <li v-if="job.location"><strong>Location:</strong> {{ job.location }}</li>
                            <li><strong>Type:</strong> {{ job.employment_type_label }}</li>
                            <li v-if="job.closing_date"><strong>Closes:</strong> {{ job.closing_date }}</li>
                        </ul>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>

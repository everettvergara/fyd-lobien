<script setup>
import SimplePager from '../../../_shared/Components/SimplePager.vue';

defineProps({
    jobs: { type: Array, default: () => [] },
    pagination: { type: Object, default: null },
});
</script>

<template>
    <section class="careers-listing lobien-section">
        <div class="lobien-container">
            <div class="lobien-section-heading">
                <h2>Open Positions</h2>
            </div>

            <div v-if="jobs.length === 0" class="careers-listing__empty text-muted text-center">
                No open positions at this time.
            </div>

            <div v-else class="careers-listing__grid row g-4 justify-content-center">
                <div v-for="job in jobs" :key="job.id" class="careers-listing__item col-md-6 col-lg-4">
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

                            <p v-if="job.summary" class="careers-job__summary card-text text-muted small mb-3">
                                {{ job.summary }}
                            </p>

                            <ul class="careers-job__meta list-unstyled small text-muted mt-auto mb-0">
                                <li v-if="job.department" class="careers-job__department">
                                    <strong>Department:</strong> {{ job.department }}
                                </li>
                                <li v-if="job.location" class="careers-job__location">
                                    <strong>Location:</strong> {{ job.location }}
                                </li>
                                <li class="careers-job__employment-type">
                                    <strong>Type:</strong> {{ job.employment_type_label }}
                                </li>
                                <li v-if="job.salary_range" class="careers-job__salary">
                                    <strong>Salary:</strong> {{ job.salary_range }}
                                </li>
                                <li v-if="job.closing_date" class="careers-job__closing-date">
                                    <strong>Closes:</strong> {{ job.closing_date }}
                                </li>
                            </ul>

                            <div
                                v-if="job.description"
                                class="careers-job__description content-body small mt-3"
                                v-html="job.description"
                            />
                            <div
                                v-if="job.requirements"
                                class="careers-job__requirements content-body small mt-2"
                                v-html="job.requirements"
                            />
                        </div>
                    </article>
                </div>
            </div>

            <SimplePager v-if="pagination" :pagination="pagination" class="careers-listing__pager mt-4" />
        </div>
    </section>
</template>

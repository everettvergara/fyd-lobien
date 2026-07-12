<script setup>
import PageContentShell from '../Components/PageContentShell.vue';
import SimplePager from '../Components/SimplePager.vue';

defineProps({
    cities: { type: Array, default: () => [] },
    pagination: { type: Object, default: null },
});
</script>

<template>
    <PageContentShell width="wide" spacing="section">
        <section class="property-listings-cities">
            <h2 class="h3 fw-bold mb-4">Browse by city</h2>

            <div v-if="cities.length === 0" class="text-muted">
                No cities with published listings yet.
            </div>

            <div v-else class="row g-4 mb-4 justify-content-center">
                <div v-for="city in cities" :key="city.slug" class="col-6 col-md-6 col-lg-3">
                    <article class="card h-100 border-0 shadow-sm">
                        <a :href="city.url" class="text-decoration-none text-reset d-flex flex-column h-100">
                            <img v-if="city.image_url"
                                 :src="city.image_url"
                                 :alt="city.image_alt"
                                 class="card-img-top"
                                 style="height: 180px; object-fit: cover;">
                            <div v-else class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                <i class="bi bi-buildings text-muted fs-1"></i>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h3 class="h5 card-title mb-1">{{ city.label }}</h3>
                                <p v-if="city.summary" class="card-text small text-muted mb-2">
                                    {{ city.summary }}
                                </p>
                                <p class="mt-auto small text-primary mb-0">
                                    {{ city.listing_count }} propert{{ city.listing_count === 1 ? 'y' : 'ies' }}
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </p>
                            </div>
                        </a>
                    </article>
                </div>
            </div>

            <SimplePager v-if="pagination" :pagination="pagination" />
        </section>
    </PageContentShell>
</template>

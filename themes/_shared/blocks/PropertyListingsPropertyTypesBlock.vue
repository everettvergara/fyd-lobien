<script setup>
import PageContentShell from '../Components/PageContentShell.vue';

defineProps({
    title: { type: String, default: '' },
    subtext: { type: String, default: '' },
    property_types: { type: Array, default: () => [] },
});
</script>

<template>
    <PageContentShell width="wide" spacing="section">
        <section class="property-listings-property-types">
            <h2 v-if="title" class="h3 fw-bold mb-4">{{ title }}</h2>
            <p v-if="subtext" class="text-muted mb-4">{{ subtext }}</p>

            <div v-if="property_types.length === 0" class="text-muted">
                No property types configured yet.
            </div>

            <div v-else class="row g-4 mb-4">
                <div v-for="propertyType in property_types" :key="propertyType.value" class="col-md-6 col-lg-4">
                    <article class="card h-100 border-0 shadow-sm">
                        <a :href="propertyType.search_url" class="text-decoration-none text-reset d-flex flex-column h-100">
                            <img v-if="propertyType.image_url"
                                 :src="propertyType.image_url"
                                 :alt="propertyType.image_alt"
                                 class="card-img-top"
                                 style="height: 180px; object-fit: cover;">
                            <div v-else class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                <i class="bi bi-grid-3x3-gap text-muted fs-1"></i>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h3 class="h5 card-title mb-1">{{ propertyType.label }}</h3>
                                <p v-if="propertyType.summary" class="card-text small text-muted mb-2">
                                    {{ propertyType.summary }}
                                </p>
                                <p class="mt-auto small text-primary mb-0">
                                    Search listings
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </p>
                            </div>
                        </a>
                    </article>
                </div>
            </div>
        </section>
    </PageContentShell>
</template>

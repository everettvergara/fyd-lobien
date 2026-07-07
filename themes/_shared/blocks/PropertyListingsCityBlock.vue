<script setup>
import PageContentShell from '../Components/PageContentShell.vue';
import PropertyListingCard from '../Components/PropertyListingCard.vue';
import PropertySearchFilterForm from '../Components/PropertySearchFilterForm.vue';
import SimplePager from '../Components/SimplePager.vue';

defineProps({
    mode: { type: String, default: 'related' },
    city_slug: { type: String, default: '' },
    city_label: { type: String, default: '' },
    city: { type: Object, default: null },
    filters: { type: Object, default: () => ({}) },
    action_url: { type: String, default: '' },
    property_types: { type: Array, default: () => [] },
    listings: { type: Array, default: () => [] },
    pagination: { type: Object, default: null },
});
</script>

<template>
    <PageContentShell width="wide" spacing="section">
        <section class="property-listings-city">
            <template v-if="mode === 'city'">
                <header class="mb-4">
                    <div class="row g-4 align-items-center">
                        <div v-if="city?.image_url" class="col-md-4">
                            <img :src="city.image_url"
                                 :alt="city.image_alt"
                                 class="img-fluid rounded-3 shadow-sm w-100"
                                 style="max-height: 260px; object-fit: cover;">
                        </div>
                        <div :class="city?.image_url ? 'col-md-8' : 'col-12'">
                            <h2 class="h3 fw-bold mb-2">Properties in {{ city_label }}</h2>
                            <p v-if="city?.summary" class="lead text-muted mb-2">{{ city.summary }}</p>
                            <div v-if="city?.description" class="text-body small" v-html="city.description"></div>
                        </div>
                    </div>
                </header>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <PropertySearchFilterForm
                            :action-url="action_url"
                            :property-types="property_types"
                            :filters="filters"
                            :show-city="false" />
                    </div>
                </div>

                <div v-if="listings.length === 0" class="text-center text-muted py-5">
                    No published listings match your filters.
                </div>

                <div v-else class="row g-4 mb-4">
                    <div v-for="listing in listings" :key="listing.id" class="col-md-6 col-lg-4">
                        <PropertyListingCard :listing="listing" />
                    </div>
                </div>

                <SimplePager v-if="pagination" :pagination="pagination" />
            </template>

            <template v-else>
                <h2 class="h3 fw-bold mb-4">
                    {{ city_label ? `More in ${city_label}` : 'Featured properties' }}
                </h2>

                <div v-if="listings.length === 0" class="text-muted">
                    No published listings available for this city.
                </div>

                <div v-else class="row g-4">
                    <div v-for="listing in listings" :key="listing.id" class="col-md-6 col-lg-4">
                        <PropertyListingCard :listing="listing" />
                    </div>
                </div>
            </template>
        </section>
    </PageContentShell>
</template>

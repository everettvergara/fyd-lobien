<script setup>
import PageContentShell from '../Components/PageContentShell.vue';
import PropertyListingCard from '../Components/PropertyListingCard.vue';
import PropertySearchFilterForm from '../Components/PropertySearchFilterForm.vue';
import SimplePager from '../Components/SimplePager.vue';

defineProps({
    filters: { type: Object, default: () => ({}) },
    action_url: { type: String, required: true },
    cities: { type: Array, default: () => [] },
    property_types: { type: Array, default: () => [] },
    listings: { type: Array, default: () => [] },
    pagination: { type: Object, default: null },
});
</script>

<template>
    <PageContentShell width="wide" spacing="section">
        <section class="property-search-results">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <PropertySearchFilterForm
                        :action-url="action_url"
                        :cities="cities"
                        :property-types="property_types"
                        :filters="filters"
                        :show-city="true" />
                </div>
            </div>

            <p class="text-muted small mb-3">
                {{ pagination?.total ?? listings.length }} propert{{ (pagination?.total ?? listings.length) === 1 ? 'y' : 'ies' }} found
            </p>

            <div v-if="listings.length === 0" class="text-center text-muted py-5">
                No properties match your search. Try widening your filters.
            </div>

            <div v-else class="row g-4 mb-4 justify-content-center">
                <div v-for="listing in listings" :key="listing.id" class="col-6 col-md-6 col-lg-3">
                    <PropertyListingCard :listing="listing" />
                </div>
            </div>

            <SimplePager v-if="pagination" :pagination="pagination" />
        </section>
    </PageContentShell>
</template>

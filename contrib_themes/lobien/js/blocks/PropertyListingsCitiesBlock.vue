<script setup>
import { computed } from 'vue';
import LobienListingsSection from '../Components/LobienListingsSection.vue';
import SimplePager from '../../../_shared/Components/SimplePager.vue';

const props = defineProps({
    cities: { type: Array, default: () => [] },
    pagination: { type: Object, default: null },
});

const items = computed(() => props.cities.map((city) => ({
    label: city.label,
    href: city.url,
    image: city.image_url,
    imageAlt: city.image_alt ?? city.label,
})));
</script>

<template>
    <template v-if="items.length">
        <LobienListingsSection
            heading="Browse by city"
            :items="items"
        />
        <div v-if="pagination" class="lobien-container">
            <SimplePager :pagination="pagination" class="mt-4 mb-5" />
        </div>
    </template>
    <section v-else class="lobien-section">
        <div class="lobien-container">
            <div class="lobien-section-heading">
                <h2>Browse by city</h2>
            </div>
            <p class="text-muted mb-0">No cities with published listings yet.</p>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';
import LobienListingsSection from '../Components/LobienListingsSection.vue';

const props = defineProps({
    title: { type: String, default: '' },
    subtext: { type: String, default: '' },
    property_types: { type: Array, default: () => [] },
});

const defaultHeading = 'Check out our wide list of available properties in the Philippines';

const items = computed(() => props.property_types.map((propertyType) => ({
    label: propertyType.label,
    href: propertyType.search_url,
    image: propertyType.image_url,
    imageAlt: propertyType.image_alt ?? propertyType.label,
})));
</script>

<template>
    <LobienListingsSection
        v-if="items.length"
        :heading="title || defaultHeading"
        :subtext="subtext"
        :items="items"
    />
    <section v-else class="lobien-section">
        <div class="lobien-container">
            <p class="text-muted mb-0">No property types configured yet.</p>
        </div>
    </section>
</template>

<script setup>
defineProps({
    sectionLabel: { type: String, default: '' },
    heading: { type: String, default: '' },
    subtext: { type: String, default: '' },
    items: { type: Array, default: () => [] },
});
</script>

<template>
    <section class="lobien-section">
        <div class="lobien-container">
            <div class="lobien-section-heading">
                <p v-if="sectionLabel" class="lobien-section-label">{{ sectionLabel }}</p>
                <h2 v-if="heading">{{ heading }}</h2>
                <p v-if="subtext">{{ subtext }}</p>
            </div>

            <div v-if="items.length" class="lobien-listings-grid">
                <div v-for="(item, i) in items" :key="i" class="lobien-listing-tile">
                    <a :href="item.href" class="lobien-listing-card">
                        <div class="lobien-listing-image">
                            <img :src="item.image" :alt="item.imageAlt ?? item.label" loading="lazy">
                        </div>
                        <div class="lobien-listing-overlay">
                            <span>{{ item.label }}</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>

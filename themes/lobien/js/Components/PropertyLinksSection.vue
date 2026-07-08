<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    type: {
        type: String,
        required: true,
        validator: (v) => ['sale', 'lease'].includes(v),
    },
});

const expanded = ref(false);

const cities = [
    'Bonifacio Global City', 'Caloocan City', 'Las Piñas City', 'Makati City',
    'Mandaluyong City', 'Manila City', 'Muntinlupa City', 'Parañaque City',
    'Pasay City', 'Pasig City', 'Quezon City', 'San Juan City', 'Taguig City',
    'Provincial', 'Others',
];

const categories = ['Industrial', 'House & Lot', 'Office & Retail'];

const title = computed(() =>
    props.type === 'sale' ? 'SEARCH PROPERTY FOR SALE' : 'SEARCH PROPERTY FOR LEASE'
);

const visibleLimit = 3;

const locations = computed(() =>
    cities.map((city) => ({
        city,
        links: categories.map((cat) => ({
            label: `${city} ${cat} For ${props.type === 'sale' ? 'Sale' : 'Lease'}`,
            href: '#',
        })),
    }))
);

function isHidden(index) {
    return !expanded.value && index >= visibleLimit;
}
</script>

<template>
    <section
        class="lobien-property-links"
        :class="{ 'lobien-property-links-lease': type === 'lease' }"
    >
        <div class="lobien-container">
            <div class="lobien-property-links-header">
                <h3>{{ title }}</h3>
                <a href="#">View Full List</a>
            </div>

            <div class="lobien-property-links-grid">
                <div
                    v-for="(loc, index) in locations"
                    :key="loc.city"
                    class="lobien-property-location"
                    :class="{ 'lobien-property-location-hidden': isHidden(index) }"
                >
                    <ul>
                        <li v-for="link in loc.links" :key="link.label">
                            <a :href="link.href">{{ link.label }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <button
                type="button"
                class="lobien-toggle-links"
                @click="expanded = !expanded"
            >
                {{ expanded ? 'Show less' : 'Show more' }}
            </button>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    hero: { type: Object, required: true },
    sliderBanner: { type: Object, default: null },
});

const page = usePage();
const contact = computed(() => page.props.app?.contact ?? {});

const bannerImage = computed(() => {
    const banner = props.sliderBanner ?? props.hero;
    return banner?.desktopImage?.url
        ?? banner?.slides?.[0]?.desktopImage?.url
        ?? 'https://www.lobiengroup.com/sites/default/files/banner/Home%20Page%20%28Alternative%20for%20BGC%20Video%20Background%29.jpg';
});

const locations = [
    'Bonifacio Global City', 'Caloocan City', 'Las Piñas City', 'Makati City',
    'Mandaluyong City', 'Manila City', 'Muntinlupa City', 'Parañaque City',
    'Pasay City', 'Pasig City', 'Quezon City', 'San Juan City', 'Taguig City',
    'Provincial', 'Others',
];

const propertyTypes = [
    'OFFICE & RETAIL FOR LEASE', 'RESIDENTIAL FOR LEASE', 'INDUSTRIAL FOR LEASE', 'LOT FOR LEASE',
    'OFFICE & RETAIL FOR SALE', 'RESIDENTIAL FOR SALE', 'INDUSTRIAL FOR SALE', 'LOT FOR SALE',
];
</script>

<template>
    <section class="lobien-hero-search">
        <div class="lobien-hero-media">
            <img :src="bannerImage" alt="Lobien Realty Group">
        </div>

        <div class="lobien-hero-form-wrap">
            <div class="lobien-hero-form">
                <h1 class="lobien-hero-form-title">Find Your Property</h1>
                <form action="#" method="get" @submit.prevent>
                    <div class="lobien-hero-form-row">
                        <select class="form-select" aria-label="Pick a location">
                            <option value="">Pick a location - Any -</option>
                            <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
                        </select>
                        <select class="form-select" aria-label="Property Type">
                            <option value="">Property Type - Any -</option>
                            <option v-for="type in propertyTypes" :key="type" :value="type">{{ type }}</option>
                        </select>
                        <select class="form-select" aria-label="Property Sub Category">
                            <option value="">Property Sub Category - Any -</option>
                            <option value="project-leasing">Project Leasing</option>
                            <option value="tenant-solutions">Tenant Solutions</option>
                            <option value="property-sale">Property Sale and Aquisitions</option>
                        </select>
                        <select class="form-select" aria-label="Lease or Sale">
                            <option value="">Lease or Sale - Any -</option>
                            <option value="lease">For Lease</option>
                            <option value="sale">For Sale</option>
                        </select>
                        <input type="text" class="form-control" placeholder="Property or building name">
                        <button type="submit" class="btn btn-search">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <div class="lobien-cta-bar">
        <div class="lobien-container">
            <p>
                Get the best properties today. Call us at
                <a v-if="contact.phone" :href="`tel:${contact.phone}`">{{ contact.phone }}</a>
                <template v-else><a href="tel:+63289839311">+632 8983 9311</a></template>
                or send us an email at
                <a v-if="contact.email" :href="`mailto:${contact.email}`">{{ contact.email }}</a>
                <template v-else><a href="mailto:inquiry@lobiengroup.com">inquiry@lobiengroup.com</a></template>
            </p>
        </div>
    </div>
</template>

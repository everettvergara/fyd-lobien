<script setup>
import { usePage } from '@inertiajs/vue3';
import lobienLogo from '../../assets/images/lobien-logo.png';

const page = usePage();
const app = page.props.app ?? {};

const tagline = 'Lobien Realty Group is the No. 1 Realty Property Provider in the Philippines. We provide office and commercial spaces, lots and properties for sale/lease in the cities of Makati, Taguig, Pasig, Mandaluyong, San Juan, Pasay, Parañaque, Las Piñas, Quezon, and Caloocan.';
</script>

<template>
    <section class="lobien-brand-strip">
        <div class="lobien-container">
            <div class="lobien-brand-strip-inner">
                <div class="lobien-brand-strip-logo">
                    <img
                        :src="app.logo || lobienLogo"
                        :alt="app.name || 'Lobien Realty Group'"
                        class="site-logo lobien-brand-strip-logo-img"
                    >
                </div>
                <p class="lobien-brand-strip-text mb-0">{{ tagline }}</p>
            </div>
        </div>
    </section>
</template>

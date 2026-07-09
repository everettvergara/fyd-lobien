<script setup>
import LobienCtaBar from '../Components/LobienCtaBar.vue';

defineProps({
    heading: { type: String, default: 'CONTACT US' },
    description: { type: String, default: '' },
    address: { type: String, default: '' },
    phones: { type: Array, default: () => [] },
    email: { type: String, default: '' },
    image_url: { type: String, default: '' },
    show_cta_bar: { type: Boolean, default: true },
});
</script>

<template>
    <section class="lobien-contact-banner lobien-page-section">
        <div class="lobien-container">
            <div class="lobien-page-section__wrapper">
                <div class="lobien-page-section__details lobien-contact-banner__details">
                    <h2 class="lobien-page-section__title">{{ heading }}</h2>
                    <div v-if="description" class="lobien-page-section__desc">
                        <p>{{ description }}</p>
                    </div>
                    <p v-if="address" class="lobien-contact-banner__address">{{ address }}</p>
                    <ul v-if="phones.length" class="lobien-contact-banner__phones">
                        <li v-for="phone in phones" :key="phone">
                            <a :href="`tel:${phone.replace(/\s/g, '')}`">{{ phone }}</a>
                        </li>
                    </ul>
                    <p v-if="email" class="lobien-contact-banner__email">
                        <a :href="`mailto:${email}`">{{ email }}</a>
                    </p>
                </div>
                <div v-if="image_url" class="lobien-page-section__image">
                    <img :src="image_url" alt="Lobien Realty Group" loading="lazy">
                </div>
            </div>
        </div>
        <LobienCtaBar v-if="show_cta_bar" />
    </section>
</template>

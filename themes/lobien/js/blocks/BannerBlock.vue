<script setup>
import { computed } from 'vue';
import BannerRenderer from '../Components/BannerRenderer.vue';
import LobienCtaBar from '../Components/LobienCtaBar.vue';

const CTA_BANNER_KEYS = [
    'about-who-we-are',
    'services-intro',
    'contact-intro',
];

const props = defineProps({
    banner: { type: Object, default: null },
    show_cta_bar: { type: Boolean, default: false },
});

const showCta = computed(() =>
    props.show_cta_bar || CTA_BANNER_KEYS.includes(props.banner?.key)
);
</script>

<template>
    <div v-if="banner" class="lobien-banner-block" :class="{ 'lobien-banner-block--with-cta': showCta }">
        <BannerRenderer :banner="banner" />
        <LobienCtaBar v-if="showCta" />
    </div>
</template>

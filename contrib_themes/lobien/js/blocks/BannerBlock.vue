<script setup>
import { computed } from 'vue';
import BannerRenderer from '../Components/BannerRenderer.vue';
import LobienCtaBar from '../Components/LobienCtaBar.vue';

const props = defineProps({
    banner: { type: Object, default: null },
    show_cta_bar: { type: Boolean, default: false },
});

const showCta = computed(() =>
    props.show_cta_bar || props.banner?.key === 'about-who-we-are'
);
</script>

<template>
    <div v-if="banner" class="lobien-banner-block">
        <BannerRenderer :banner="banner" />
        <LobienCtaBar v-if="showCta" />
    </div>
</template>

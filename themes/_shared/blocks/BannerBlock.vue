<script setup>
import BannerRenderer from '../../fyd-default/js/Components/BannerRenderer.vue';

defineProps({
    banner: { type: Object, default: null },
});
</script>

<template>
    <BannerRenderer v-if="banner" :banner="banner" />
</template>

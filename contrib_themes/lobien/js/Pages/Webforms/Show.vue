<script setup>
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SeoHead from '../../Components/SeoHead.vue';
import WebformRenderer from '../../Components/WebformRenderer.vue';

defineProps({
    webform: { type: Object, required: true },
    seo: { type: Object, default: () => ({}) },
});
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="seo" />

        <div class="lobien-container py-5">
            <h1 class="fw-normal text-uppercase mb-4">{{ webform.name }}</h1>
            <WebformRenderer :slug="webform.slug" />
        </div>
    </PublicLayout>
</template>

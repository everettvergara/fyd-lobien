<script setup>
import LobienNewsletterOutlookSection from '../Components/LobienNewsletterOutlookSection.vue';

defineProps({
    slug: { type: String, default: '' },
    name: { type: String, default: '' },
    description: { type: String, default: '' },
});
</script>

<template>
    <LobienNewsletterOutlookSection
        v-if="slug"
        :slug="slug"
        :name="name"
        :description="description"
    />
</template>

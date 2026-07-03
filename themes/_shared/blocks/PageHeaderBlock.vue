<script setup>
import PageContentShell from '../Components/PageContentShell.vue';

defineProps({
    title: { type: String, default: '' },
    summary: { type: String, default: '' },
    showSummary: { type: Boolean, default: true },
});
</script>

<template>
    <PageContentShell spacing="header">
        <header class="page-header-block">
            <h1 class="page-header-block__title">{{ title }}</h1>
            <p v-if="showSummary && summary" class="page-header-block__summary">{{ summary }}</p>
        </header>
    </PageContentShell>
</template>

<script setup>
import PageContentShell from '../Components/PageContentShell.vue';

defineProps({
    body: { type: String, default: '' },
});
</script>

<template>
    <PageContentShell v-if="body" spacing="body">
        <div class="page-body-block content-body" v-html="body" />
    </PageContentShell>
</template>

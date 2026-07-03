<script setup>
import PageContentShell from '../Components/PageContentShell.vue';
import WebformRenderer from '../Components/WebformRenderer.vue';

defineProps({
    slug: { type: String, default: '' },
});
</script>

<template>
    <PageContentShell v-if="slug" spacing="section">
        <div class="page-form-block card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <WebformRenderer :slug="slug" show-title />
            </div>
        </div>
    </PageContentShell>
</template>

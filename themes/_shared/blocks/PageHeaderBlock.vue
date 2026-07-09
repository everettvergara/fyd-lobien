<script setup>
import PageContentShell from '../Components/PageContentShell.vue';

defineProps({
    title: { type: String, default: '' },
    summary: { type: String, default: '' },
    showSummary: { type: Boolean, default: true },
    featuredImage: { type: Object, default: null },
    author: { type: String, default: '' },
    publishedAt: { type: String, default: '' },
    contentType: { type: Object, default: null },
});

function formatPublishedAt(value) {
    if (! value) {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <PageContentShell spacing="header">
        <header class="page-header-block">
            <img
                v-if="featuredImage?.url"
                :src="featuredImage.url"
                :alt="featuredImage.alt"
                class="page-header-block__featured-image img-fluid rounded mb-4 w-100"
            >

            <p
                v-if="contentType?.label || author || publishedAt"
                class="page-header-block__meta text-muted small mb-2"
            >
                <span v-if="contentType?.label">{{ contentType.label }}</span>
                <span v-if="contentType?.label && author"> · </span>
                <span v-if="author">{{ author }}</span>
                <span v-if="(contentType?.label || author) && publishedAt"> · </span>
                <span v-if="publishedAt">{{ formatPublishedAt(publishedAt) }}</span>
            </p>

            <h1 class="page-header-block__title">{{ title }}</h1>
            <p v-if="showSummary && summary" class="page-header-block__summary">{{ summary }}</p>
        </header>
    </PageContentShell>
</template>

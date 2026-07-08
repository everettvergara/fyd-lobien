<script setup>
import PublicLayout from '../Layouts/PublicLayout.vue';
import SeoHead from '../Components/SeoHead.vue';
import LobienHomeContent from '../Components/LobienHomeContent.vue';
import { computed } from 'vue';

const props = defineProps({
    hero: { type: Object, default: () => ({}) },
    sliderBanner: { type: Object, default: null },
    latestArticles: { type: Array, default: () => [] },
    featuredContent: { type: Array, default: () => [] },
    seo: { type: Object, default: () => ({}) },
});

const latestArticlesBlock = computed(() => {
    if (! props.latestArticles.length) {
        return null;
    }

    return {
        key: 'latest-articles',
        rows: props.latestArticles.map((article) => [
            { field: 'title', value: article.title },
            { field: 'summary', value: article.summary },
            { field: 'published_at', value: article.publishedAt },
            { field: 'featured_image', value: article.featuredImage },
            { field: 'slug', value: article.slug },
        ]),
    };
});
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="seo" />

        <LobienHomeContent
            :hero-banner="sliderBanner ?? hero"
            :latest-articles-block="latestArticlesBlock"
        />
    </PublicLayout>
</template>

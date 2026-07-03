<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    seo: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>
        <meta v-if="seo.description" name="description" :content="seo.description" />
        <meta v-if="seo.keywords" name="keywords" :content="seo.keywords" />
        <meta v-if="seo.robots" name="robots" :content="seo.robots" />
        <link v-if="seo.canonical" rel="canonical" :href="seo.canonical" />
        <meta v-if="seo.og?.title" property="og:title" :content="seo.og.title" />
        <meta v-if="seo.og?.description" property="og:description" :content="seo.og.description" />
    </Head>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PageSection from '@/Components/PageSection.vue';

defineProps({
    page: { type: Object, required: true },
});
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="page.seo" />

        <div class="container py-5">
            <Breadcrumb :items="[{ label: page.title }]" />

            <article>
                <header class="mb-4">
                    <h1 class="display-5 fw-bold">{{ page.title }}</h1>
                    <p v-if="page.summary" class="lead text-muted">{{ page.summary }}</p>
                </header>

                <img
                    v-if="page.featuredImage"
                    :src="page.featuredImage.url"
                    :alt="page.featuredImage.alt"
                    class="img-fluid rounded mb-4"
                />

                <div v-if="page.content" class="mb-5 content-body" style="white-space: pre-wrap;">{{ page.content }}</div>

                <PageSection
                    v-for="(section, index) in page.sections"
                    :key="index"
                    :section="section"
                />
            </article>
        </div>
    </PublicLayout>
</template>

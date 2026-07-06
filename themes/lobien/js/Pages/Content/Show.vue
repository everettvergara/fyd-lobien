<script setup>
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SeoHead from '../../Components/SeoHead.vue';
import Breadcrumb from '../../Components/Breadcrumb.vue';
import BannerRenderer from '../../Components/BannerRenderer.vue';
import WebformRenderer from '../../../../_shared/Components/WebformRenderer.vue';

defineProps({
    content: { type: Object, required: true },
    banner: { type: Object, default: null },
    webform: { type: Object, default: null },
});
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="content.seo" />

        <BannerRenderer
            v-if="banner"
            :banner="banner"
        />

        <div class="lobien-container py-5">
            <Breadcrumb :items="[{ label: content.title }]" />

            <article>
                <header v-if="!banner" class="mb-4">
                    <p v-if="content.contentType" class="lobien-section-label mb-2">{{ content.contentType.label }}</p>
                    <h1 class="fw-normal text-uppercase">{{ content.title }}</h1>
                    <p v-if="content.summary" class="text-muted">{{ content.summary }}</p>
                </header>

                <img
                    v-if="content.featuredImage"
                    :src="content.featuredImage.url"
                    :alt="content.featuredImage.alt"
                    class="img-fluid mb-4"
                />

                <div v-if="content.body" class="mb-5 content-body" v-html="content.body" />

                <WebformRenderer v-if="webform" :slug="webform.slug" class="mt-2" />
            </article>
        </div>
    </PublicLayout>
</template>

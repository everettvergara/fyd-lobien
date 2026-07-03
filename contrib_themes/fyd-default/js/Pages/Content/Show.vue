<script setup>
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SeoHead from '../../Components/SeoHead.vue';
import Breadcrumb from '../../Components/Breadcrumb.vue';
import BannerRenderer from '../../Components/BannerRenderer.vue';
import WebformRenderer from '../../Components/WebformRenderer.vue';
import NewsletterRenderer from '../../Components/NewsletterRenderer.vue';
import CareersListingRenderer from '../../Components/CareersListingRenderer.vue';

defineProps({
    content: { type: Object, required: true },
    banner: { type: Object, default: null },
    webform: { type: Object, default: null },
    newsletter: { type: Object, default: null },
    careersListing: { type: Boolean, default: false },
});
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="content.seo" />

        <BannerRenderer
            v-if="banner"
            :banner="banner"
        />

        <div class="container py-5">
            <Breadcrumb :items="[{ label: content.title }]" />

            <article>
                <header v-if="!banner" class="mb-4">
                    <p v-if="content.contentType" class="text-muted small text-uppercase mb-2">{{ content.contentType.label }}</p>
                    <h1 class="display-5 fw-bold">{{ content.title }}</h1>
                    <p v-if="content.summary" class="lead text-muted">{{ content.summary }}</p>
                </header>

                <img
                    v-if="content.featuredImage"
                    :src="content.featuredImage.url"
                    :alt="content.featuredImage.alt"
                    class="img-fluid rounded mb-4"
                />

                <div v-if="content.body" class="mb-5 content-body" v-html="content.body" />

                <CareersListingRenderer v-if="careersListing" class="mb-5" />

                <WebformRenderer v-if="webform" :slug="webform.slug" class="mt-2" />

                <NewsletterRenderer v-if="newsletter" :slug="newsletter.slug" class="mt-4" />
            </article>
        </div>
    </PublicLayout>
</template>

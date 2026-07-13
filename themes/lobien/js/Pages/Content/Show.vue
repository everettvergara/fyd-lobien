<script setup>
import { computed } from 'vue';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SeoHead from '../../Components/SeoHead.vue';
import Breadcrumb from '../../Components/Breadcrumb.vue';
import BannerRenderer from '../../Components/BannerRenderer.vue';
import WebformRenderer from '../../Components/WebformRenderer.vue';
import NewsletterRenderer from '../../Components/NewsletterRenderer.vue';
import CareersListingRenderer from '../../Components/CareersListingRenderer.vue';
import { resolveYoutubeEmbedUrl } from '../../utils/youtube.js';

const props = defineProps({
    content: { type: Object, required: true },
    banner: { type: Object, default: null },
    webform: { type: Object, default: null },
    newsletter: { type: Object, default: null },
    careersListing: { type: Boolean, default: false },
});

const youtubeSrc = computed(() => resolveYoutubeEmbedUrl(
    props.content?.urlLink,
    props.content?.summary,
    props.content?.body,
));
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

                <div
                    v-if="youtubeSrc"
                    class="lobien-content-video lobien-article-video mb-4"
                >
                    <iframe
                        :src="youtubeSrc"
                        :title="content.title || 'YouTube video'"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        referrerpolicy="strict-origin-when-cross-origin"
                    />
                </div>
                <img
                    v-else-if="content.featuredImage"
                    :src="content.featuredImage.url"
                    :alt="content.featuredImage.alt"
                    class="img-fluid mb-4"
                />

                <div v-if="content.body" class="mb-5 content-body" v-html="content.body" />

                <CareersListingRenderer v-if="careersListing" class="mb-5" />

                <WebformRenderer v-if="webform" :slug="webform.slug" class="mt-2" />

                <NewsletterRenderer v-if="newsletter" :slug="newsletter.slug" class="mt-4" />
            </article>
        </div>
    </PublicLayout>
</template>

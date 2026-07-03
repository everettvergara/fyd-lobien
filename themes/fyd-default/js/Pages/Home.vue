<script setup>
import PublicLayout from '../Layouts/PublicLayout.vue';
import SeoHead from '../Components/SeoHead.vue';
import BannerRenderer from '../Components/BannerRenderer.vue';
import Carousel from '../Components/Carousel.vue';
import ContentCard from '../Components/ContentCard.vue';
import ContactBlock from '../Components/ContactBlock.vue';

defineProps({
    hero: { type: Object, required: true },
    sliderBanner: { type: Object, default: null },
    latestArticles: { type: Array, default: () => [] },
    featuredContent: { type: Array, default: () => [] },
    seo: { type: Object, default: () => ({}) },
});
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="seo" />

        <Carousel
            v-if="sliderBanner?.slides?.length > 1"
            :banner="sliderBanner"
        />
        <BannerRenderer
            v-else-if="sliderBanner"
            :banner="sliderBanner"
        />
        <BannerRenderer
            v-else
            :banner="hero"
        />

        <section v-if="featuredContent.length" class="public-section">
            <div class="container">
                <h2 class="fw-bold text-center mb-5">Featured Content</h2>
                <div class="row g-4">
                    <div v-for="(item, i) in featuredContent" :key="i" class="col-md-4">
                        <ContentCard :content="item" />
                    </div>
                </div>
            </div>
        </section>

        <section v-if="latestArticles.length" class="public-section public-section-alt">
            <div class="container">
                <h2 class="fw-bold text-center mb-5">Latest Articles</h2>
                <div class="row g-4">
                    <div v-for="(article, i) in latestArticles" :key="i" class="col-md-4">
                        <ContentCard :content="article" />
                    </div>
                </div>
            </div>
        </section>

        <section class="public-section">
            <div class="container">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-8">
                        <h2 id="about" class="fw-bold">Built for Growth</h2>
                        <p class="text-muted lead">
                            A professional corporate website platform powered by FYD CMS.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <ContactBlock />
    </PublicLayout>
</template>

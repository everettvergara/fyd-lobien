<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import HeroBanner from '@/Components/HeroBanner.vue';
import Carousel from '@/Components/Carousel.vue';
import PostCard from '@/Components/PostCard.vue';
import PageCard from '@/Components/PageCard.vue';
import ContactBlock from '@/Components/ContactBlock.vue';

defineProps({
    hero: { type: Object, required: true },
    slider: { type: Array, default: () => [] },
    latestPosts: { type: Array, default: () => [] },
    featuredPages: { type: Array, default: () => [] },
    seo: { type: Object, default: () => ({}) },
});
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="seo" />

        <Carousel v-if="slider.length" :banners="slider" />
        <HeroBanner
            v-else
            :title="hero.title"
            :subtitle="hero.subtitle"
            :description="hero.description"
            :button-text="hero.buttonText"
            :button-url="hero.buttonUrl"
            :desktop-image="hero.desktopImage"
        />

        <section v-if="featuredPages.length" class="public-section">
            <div class="container">
                <h2 class="fw-bold text-center mb-5">Featured Pages</h2>
                <div class="row g-4">
                    <div v-for="(page, i) in featuredPages" :key="i" class="col-md-4">
                        <PageCard :page="page" />
                    </div>
                </div>
            </div>
        </section>

        <section v-if="latestPosts.length" class="public-section public-section-alt">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <h2 class="fw-bold mb-0">Latest News</h2>
                    <a href="/blog" class="btn btn-outline-primary btn-sm">View All</a>
                </div>
                <div class="row g-4">
                    <div v-for="(post, i) in latestPosts" :key="i" class="col-md-4">
                        <PostCard :post="post" />
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

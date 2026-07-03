<script setup>
import PublicLayout from '../Layouts/PublicLayout.vue';
import SeoHead from '../Components/SeoHead.vue';
import PropertySearchHero from '../Components/PropertySearchHero.vue';
import ListingCategoryGrid from '../Components/ListingCategoryGrid.vue';
import AgentConsultBlock from '../Components/AgentConsultBlock.vue';
import LobienArticleCard from '../Components/LobienArticleCard.vue';
import MarketOutlookBlock from '../Components/MarketOutlookBlock.vue';
import PropertyLinksSection from '../Components/PropertyLinksSection.vue';

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

        <PropertySearchHero :hero="hero" :slider-banner="sliderBanner" />

        <ListingCategoryGrid />

        <AgentConsultBlock />

        <section v-if="latestArticles.length" class="lobien-section">
            <div class="lobien-container">
                <div class="lobien-section-heading">
                    <h2>What's New?</h2>
                    <p>Don't miss out on the latest news and updates about the Philippine real estate industry</p>
                </div>
                <div class="lobien-news-grid">
                    <div v-for="(article, i) in latestArticles" :key="i" class="lobien-news-item">
                        <LobienArticleCard :content="article" />
                    </div>
                </div>
            </div>
        </section>

        <MarketOutlookBlock />

        <PropertyLinksSection type="sale" />
        <PropertyLinksSection type="lease" />
    </PublicLayout>
</template>

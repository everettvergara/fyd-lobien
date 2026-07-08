<script setup>
import PropertySearchHero from './PropertySearchHero.vue';
import ListingCategoryGrid from './ListingCategoryGrid.vue';
import AgentConsultBlock from './AgentConsultBlock.vue';
import LobienWhatsNewSection from './LobienWhatsNewSection.vue';
import MarketOutlookBlock from './MarketOutlookBlock.vue';
import PropertyLinksSection from './PropertyLinksSection.vue';

defineProps({
    heroBanner: { type: Object, default: null },
    latestArticlesBlock: { type: Object, default: null },
});
</script>

<template>
    <PropertySearchHero :hero="heroBanner ?? {}" />

    <ListingCategoryGrid />

    <AgentConsultBlock />

    <LobienWhatsNewSection :content-block="latestArticlesBlock" />

    <MarketOutlookBlock />

    <PropertyLinksSection type="sale" />
    <PropertyLinksSection type="lease" />
</template>

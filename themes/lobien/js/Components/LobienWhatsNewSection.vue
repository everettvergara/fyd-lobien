<script setup>
import { computed } from 'vue';
import LobienArticleCard from './LobienArticleCard.vue';
import { mapContentBlockRowsToArticles } from '../utils/mapContentBlockRows.js';

const props = defineProps({
    contentBlock: { type: Object, default: null },
});

const articles = computed(() => mapContentBlockRowsToArticles(props.contentBlock, 'articles'));
</script>

<template>
    <section v-if="articles.length" class="lobien-section">
        <div class="lobien-container">
            <div class="lobien-section-heading">
                <h2>What's New?</h2>
                <p>Don't miss out on the latest news and updates about the Philippine real estate industry</p>
            </div>
            <div class="lobien-news-grid">
                <div v-for="(article, i) in articles" :key="article.path || i" class="lobien-news-item">
                    <LobienArticleCard :content="article" />
                </div>
            </div>
        </div>
    </section>
</template>

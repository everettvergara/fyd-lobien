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
            <div v-if="contentBlock?.title || contentBlock?.summary" class="lobien-section-heading">
                <h2 v-if="contentBlock?.title">{{ contentBlock.title }}</h2>
                <p v-if="contentBlock?.summary">{{ contentBlock.summary }}</p>
            </div>
            <div class="lobien-news-grid">
                <div v-for="(article, i) in articles" :key="article.path || i" class="lobien-news-item">
                    <LobienArticleCard :content="article" />
                </div>
            </div>
        </div>
    </section>
</template>

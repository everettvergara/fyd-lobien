<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import LobienArticleCard from './LobienArticleCard.vue';
import { mapContentBlockRowsToArticles } from '../utils/mapContentBlockRows.js';

const props = defineProps({
    contentBlock: { type: Object, default: null },
});

const articles = computed(() => mapContentBlockRowsToArticles(props.contentBlock));
const pagination = computed(() => props.contentBlock?.pagination ?? null);

function pageUrl(pageNumber) {
    if (!pagination.value) {
        return '#';
    }

    const url = new URL(window.location.href);
    url.searchParams.set(pagination.value.queryParam, String(pageNumber));

    return `${url.pathname}${url.search}${url.hash}`;
}
</script>

<template>
    <section
        v-if="contentBlock"
        :id="contentBlock.wrapperId"
        class="lobien-bulletin-listing lobien-section"
    >
        <div v-if="contentBlock?.title || contentBlock?.summary" class="lobien-section-heading">
            <h2 v-if="contentBlock?.title">{{ contentBlock.title }}</h2>
            <p v-if="contentBlock?.summary">{{ contentBlock.summary }}</p>
        </div>

        <div v-if="articles.length === 0" class="lobien-bulletin-listing__empty text-muted text-center">
            No published entries yet.
        </div>

        <div v-else class="lobien-bulletin-grid">
            <div
                v-for="(article, index) in articles"
                :key="article.path || article.titleHref || index"
                class="lobien-bulletin-grid__item"
            >
                <LobienArticleCard :content="article" />
            </div>
        </div>

        <nav
            v-if="pagination && pagination.lastPage > 1"
            class="lobien-bulletin-listing__pagination"
            aria-label="LRG Bulletin pagination"
        >
            <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: pagination.currentPage <= 1 }">
                    <Link
                        v-if="pagination.currentPage > 1"
                        :href="pageUrl(1)"
                        class="page-link"
                        aria-label="First page"
                    >First &laquo;</Link>
                    <span v-else class="page-link">First &laquo;</span>
                </li>
                <li class="page-item" :class="{ disabled: pagination.currentPage <= 1 }">
                    <Link
                        v-if="pagination.currentPage > 1"
                        :href="pageUrl(pagination.currentPage - 1)"
                        class="page-link"
                        rel="prev"
                        aria-label="Previous page"
                    >&lsaquo;</Link>
                    <span v-else class="page-link">&lsaquo;</span>
                </li>
                <li
                    v-for="pageNumber in pagination.lastPage"
                    :key="pageNumber"
                    class="page-item"
                    :class="{ active: pageNumber === pagination.currentPage }"
                >
                    <Link :href="pageUrl(pageNumber)" class="page-link">{{ pageNumber }}</Link>
                </li>
                <li class="page-item" :class="{ disabled: pagination.currentPage >= pagination.lastPage }">
                    <Link
                        v-if="pagination.currentPage < pagination.lastPage"
                        :href="pageUrl(pagination.currentPage + 1)"
                        class="page-link"
                        rel="next"
                        aria-label="Next page"
                    >&rsaquo;</Link>
                    <span v-else class="page-link">&rsaquo;</span>
                </li>
                <li class="page-item" :class="{ disabled: pagination.currentPage >= pagination.lastPage }">
                    <Link
                        v-if="pagination.currentPage < pagination.lastPage"
                        :href="pageUrl(pagination.lastPage)"
                        class="page-link"
                        aria-label="Last page"
                    >Last &raquo;</Link>
                    <span v-else class="page-link">Last &raquo;</span>
                </li>
            </ul>
        </nav>
    </section>
</template>

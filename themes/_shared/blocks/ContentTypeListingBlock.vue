<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    listing: { type: Object, default: null },
});

const items = computed(() => props.listing?.items ?? []);
const pagination = computed(() => props.listing?.pagination ?? null);

function pageUrl(pageNumber) {
    if (! pagination.value) {
        return '#';
    }

    const url = new URL(window.location.href);
    url.searchParams.set(pagination.value.queryParam, String(pageNumber));

    return `${url.pathname}${url.search}${url.hash}`;
}
</script>

<template>
    <section v-if="listing" class="content-type-listing">
        <header v-if="listing.contentType?.label" class="content-type-listing__header">
            <h1 class="content-type-listing__title">{{ listing.contentType.label }}</h1>
        </header>

        <div v-if="items.length === 0" class="content-type-listing__empty">
            No published entries yet.
        </div>

        <div v-else class="content-type-listing__items">
            <article
                v-for="item in items"
                :key="item.path"
                class="content-type-listing__item"
            >
                <h2 class="content-type-listing__item-title">
                    <Link :href="`/${item.path}`" class="content-type-listing__item-link">
                        {{ item.title }}
                    </Link>
                </h2>
                <p v-if="item.summary" class="content-type-listing__item-summary">{{ item.summary }}</p>
                <time v-if="item.publishedAt" class="content-type-listing__item-date" :datetime="item.publishedAt">
                    {{ item.publishedAt }}
                </time>
            </article>
        </div>

        <nav
            v-if="pagination && pagination.lastPage > 1"
            class="content-type-listing__pagination"
            aria-label="Content type listing pagination"
        >
            <ul class="pagination">
                <li
                    v-for="pageNumber in pagination.lastPage"
                    :key="pageNumber"
                    class="page-item"
                    :class="{ active: pageNumber === pagination.currentPage }"
                >
                    <Link :href="pageUrl(pageNumber)" class="page-link">{{ pageNumber }}</Link>
                </li>
            </ul>
        </nav>
    </section>
</template>

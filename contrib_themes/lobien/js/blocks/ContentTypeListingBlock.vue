<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import LobienArticleCard from '../Components/LobienArticleCard.vue';

const props = defineProps({
    listing: { type: Object, default: null },
});

const LINK_FIRST_TYPES = ['videos', 'property_tours', 'social_media'];

const typeKey = computed(() => props.listing?.contentType?.key ?? '');
const isLinkFirst = computed(() => LINK_FIRST_TYPES.includes(typeKey.value));
const isTwoColumn = computed(() => typeKey.value === 'property_tours');

const readMoreLabel = computed(() => {
    if (typeKey.value === 'article') {
        return 'Read more';
    }

    if (typeKey.value === 'downloadable') {
        return 'Download';
    }

    return 'View';
});

const items = computed(() => {
    const raw = props.listing?.items ?? [];

    return raw.map((item) => {
        if (isLinkFirst.value) {
            if (item.urlLink) {
                return {
                    ...item,
                    titleHref: item.urlLink,
                    imageHref: item.featuredImage ? item.urlLink : null,
                    path: null,
                };
            }

            return {
                ...item,
                titleHref: item.path ? `/${item.path}` : null,
                imageHref: null,
            };
        }

        const href = item.path ? `/${item.path}` : (item.urlLink ?? null);

        if (typeKey.value === 'downloadable') {
            const downloadHref = item.attachment?.url
                ?? (item.path ? `/${item.path}` : null)
                ?? item.urlLink
                ?? null;

            return {
                ...item,
                titleHref: downloadHref,
                imageHref: item.featuredImage ? downloadHref : null,
                path: item.attachment?.url ? null : item.path,
                urlLink: item.attachment?.url ?? item.urlLink,
            };
        }

        return {
            ...item,
            titleHref: href,
            imageHref: item.featuredImage ? href : null,
        };
    });
});

const pagination = computed(() => props.listing?.pagination ?? null);

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
        v-if="listing"
        class="lobien-bulletin-listing lobien-section content-type-listing"
        :class="{ 'lobien-bulletin-listing--two-col': isTwoColumn }"
    >
        <div
            v-if="listing.contentType?.label"
            class="lobien-section-heading"
        >
            <h2>{{ listing.contentType.label }}</h2>
        </div>

        <div
            v-if="items.length === 0"
            class="lobien-bulletin-listing__empty text-muted text-center"
        >
            No published entries yet.
        </div>

        <div
            v-else
            class="lobien-bulletin-grid"
        >
            <div
                v-for="(item, index) in items"
                :key="item.path || item.urlLink || item.title || index"
                class="lobien-bulletin-grid__item"
            >
                <LobienArticleCard
                    :content="item"
                    :read-more-label="readMoreLabel"
                />
            </div>
        </div>

        <nav
            v-if="pagination && pagination.lastPage > 1"
            class="lobien-bulletin-listing__pagination"
            aria-label="Content type listing pagination"
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

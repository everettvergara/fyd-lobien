<script setup>
import { computed } from 'vue';

const props = defineProps({
    pagination: { type: Object, required: true },
});

const WINDOW_RADIUS = 5;

const pages = computed(() => {
    const last = Math.max(1, Number(props.pagination?.last_page ?? 1));
    const current = Math.min(last, Math.max(1, Number(props.pagination?.current_page ?? 1)));

    if (last <= 10) {
        return Array.from({ length: last }, (_, index) => index + 1);
    }

    const numbers = new Set([1, last]);
    const start = Math.max(1, current - WINDOW_RADIUS);
    const end = Math.min(last, current + WINDOW_RADIUS);

    for (let page = start; page <= end; page += 1) {
        numbers.add(page);
    }

    const sorted = [...numbers].sort((a, b) => a - b);
    const items = [];

    for (let index = 0; index < sorted.length; index += 1) {
        const page = sorted[index];
        const previous = sorted[index - 1];

        if (previous !== undefined && page - previous > 1) {
            items.push('…');
        }

        items.push(page);
    }

    return items;
});

function urlForPage(page) {
    if (typeof window === 'undefined') {
        return `?page=${page}`;
    }

    const params = new URLSearchParams(window.location.search);
    params.set('page', String(page));

    return `${window.location.pathname}?${params.toString()}`;
}
</script>

<template>
    <nav v-if="pagination && pagination.last_page > 1" aria-label="Pagination">
        <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
                <a class="page-link" :href="urlForPage(pagination.current_page - 1)" rel="prev">&laquo;</a>
            </li>
            <li v-for="(page, index) in pages"
                :key="`${page}-${index}`"
                class="page-item"
                :class="{
                    active: page === pagination.current_page,
                    disabled: page === '…',
                }">
                <span v-if="page === '…'" class="page-link">{{ page }}</span>
                <a v-else class="page-link" :href="urlForPage(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                <a class="page-link" :href="urlForPage(pagination.current_page + 1)" rel="next">&raquo;</a>
            </li>
        </ul>
    </nav>
</template>

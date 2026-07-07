<script setup>
import { computed } from 'vue';

const props = defineProps({
    pagination: { type: Object, required: true },
});

const pages = computed(() => {
    const last = props.pagination?.last_page ?? 1;
    return Array.from({ length: last }, (_, index) => index + 1);
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
            <li v-for="page in pages"
                :key="page"
                class="page-item"
                :class="{ active: page === pagination.current_page }">
                <a class="page-link" :href="urlForPage(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                <a class="page-link" :href="urlForPage(pagination.current_page + 1)" rel="next">&raquo;</a>
            </li>
        </ul>
    </nav>
</template>

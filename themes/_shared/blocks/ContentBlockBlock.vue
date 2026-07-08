<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ContentBlockCell from './ContentBlockCell.vue';

const props = defineProps({
    contentBlock: { type: Object, default: null },
});

const block = computed(() => props.contentBlock);

function pageUrl(pageNumber) {
    if (! block.value?.pagination) {
        return '#';
    }

    const url = new URL(window.location.href);
    url.searchParams.set(block.value.pagination.queryParam, String(pageNumber));

    return `${url.pathname}${url.search}${url.hash}`;
}
</script>

<template>
    <section
        v-if="block && (block.summary || block.rows?.length)"
        :id="block.wrapperId"
        :class="block.wrapperClass"
    >
        <p v-if="block.summary" class="content-block__summary text-muted mb-4">{{ block.summary }}</p>

        <div v-if="block.rows?.length" :class="['content-block__format', `content-block__format--${block.formatter}`]">
            <table v-if="block.formatter === 'table'" class="table content-block__table">
                <thead>
                    <tr>
                        <th
                            v-for="field in block.fields"
                            :key="field.field"
                            :class="field.class"
                            :id="`${field.id}-header`"
                        >
                            {{ field.label }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, rowIndex) in block.rows" :key="rowIndex" class="content-block__row">
                        <td
                            v-for="cell in row"
                            :key="cell.field"
                            :class="cell.class"
                            :id="cell.id"
                        >
                            <ContentBlockCell :cell="cell" />
                        </td>
                    </tr>
                </tbody>
            </table>

            <component
                :is="block.formatter === 'ol' ? 'ol' : 'ul'"
                v-else-if="block.formatter === 'ol' || block.formatter === 'ul'"
                class="content-block__list"
            >
                <li v-for="(row, rowIndex) in block.rows" :key="rowIndex" class="content-block__item">
                    <div
                        v-for="cell in row"
                        :key="cell.field"
                        :class="['content-block__field', cell.class]"
                        :id="cell.id"
                    >
                        <ContentBlockCell :cell="cell" :show-label="block.formatter !== 'unformatted'" />
                    </div>
                </li>
            </component>

            <div v-else>
                <div v-for="(row, rowIndex) in block.rows" :key="rowIndex" class="content-block__item">
                    <div
                        v-for="cell in row"
                        :key="cell.field"
                        :class="['content-block__field', cell.class]"
                        :id="cell.id"
                    >
                        <ContentBlockCell :cell="cell" />
                    </div>
                </div>
            </div>
        </div>

        <nav v-if="block.rows?.length && block.pagination && block.pagination.lastPage > 1" class="mt-3" aria-label="Content block pagination">
            <ul class="pagination content-block__pagination">
                <li v-for="pageNumber in block.pagination.lastPage" :key="pageNumber" class="page-item" :class="{ active: pageNumber === block.pagination.currentPage }">
                    <Link :href="pageUrl(pageNumber)" class="page-link">{{ pageNumber }}</Link>
                </li>
            </ul>
        </nav>
    </section>
</template>

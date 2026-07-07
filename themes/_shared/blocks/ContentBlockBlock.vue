<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    contentBlock: { type: Object, default: null },
});

const block = computed(() => props.contentBlock);

function fieldComponent(field) {
    if (field.field === 'featured_image' && field.value && typeof field.value === 'object') {
        return 'image';
    }

    if (field.field === 'attachment' && field.value && typeof field.value === 'object') {
        return 'file';
    }

    if (field.field === 'url_link' && field.value) {
        return 'link';
    }

    if (field.field === 'body') {
        return 'html';
    }

    return 'text';
}

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
        v-if="block && block.rows?.length"
        :id="block.wrapperId"
        :class="block.wrapperClass"
    >
        <div :class="['content-block__format', `content-block__format--${block.formatter}`]">
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
                            <img
                                v-if="fieldComponent(cell) === 'image'"
                                :src="cell.value.url"
                                :alt="cell.value.alt"
                                class="content-block__image"
                            >
                            <a
                                v-else-if="fieldComponent(cell) === 'link'"
                                :href="cell.value"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="content-block__link"
                            >{{ cell.value }}</a>
                            <a
                                v-else-if="fieldComponent(cell) === 'file'"
                                :href="cell.value.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="content-block__file"
                            >{{ cell.value.label }}</a>
                            <span v-else-if="fieldComponent(cell) === 'html'" v-html="cell.value" />
                            <span v-else>{{ cell.value }}</span>
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
                        <span v-if="block.formatter !== 'unformatted'" class="content-block__field-label">{{ cell.label }}: </span>
                        <img
                            v-if="fieldComponent(cell) === 'image'"
                            :src="cell.value.url"
                            :alt="cell.value.alt"
                            class="content-block__image"
                        >
                        <a
                            v-else-if="fieldComponent(cell) === 'link'"
                            :href="cell.value"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="content-block__link"
                        >{{ cell.value }}</a>
                        <a
                            v-else-if="fieldComponent(cell) === 'file'"
                            :href="cell.value.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="content-block__file"
                        >{{ cell.value.label }}</a>
                        <span v-else-if="fieldComponent(cell) === 'html'" v-html="cell.value" />
                        <span v-else>{{ cell.value }}</span>
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
                        <img
                            v-if="fieldComponent(cell) === 'image'"
                            :src="cell.value.url"
                            :alt="cell.value.alt"
                            class="content-block__image"
                        >
                        <a
                            v-else-if="fieldComponent(cell) === 'link'"
                            :href="cell.value"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="content-block__link"
                        >{{ cell.value }}</a>
                        <a
                            v-else-if="fieldComponent(cell) === 'file'"
                            :href="cell.value.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="content-block__file"
                        >{{ cell.value.label }}</a>
                        <div v-else-if="fieldComponent(cell) === 'html'" v-html="cell.value" />
                        <div v-else>{{ cell.value }}</div>
                    </div>
                </div>
            </div>
        </div>

        <nav v-if="block.pagination && block.pagination.lastPage > 1" class="mt-3" aria-label="Content block pagination">
            <ul class="pagination content-block__pagination">
                <li v-for="pageNumber in block.pagination.lastPage" :key="pageNumber" class="page-item" :class="{ active: pageNumber === block.pagination.currentPage }">
                    <Link :href="pageUrl(pageNumber)" class="page-link">{{ pageNumber }}</Link>
                </li>
            </ul>
        </nav>
    </section>
</template>

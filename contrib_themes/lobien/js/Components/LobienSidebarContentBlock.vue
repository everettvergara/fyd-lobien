<script setup>
import { computed } from 'vue';
import ContentBlockCell from '../../../_shared/blocks/ContentBlockCell.vue';

const props = defineProps({
    contentBlock: { type: Object, default: null },
});

const rows = computed(() => props.contentBlock?.rows ?? []);
</script>

<template>
    <section
        v-if="contentBlock"
        :id="contentBlock.wrapperId"
        :class="['lobien-sidebar-content-block', contentBlock.wrapperClass]"
    >
        <h2
            v-if="contentBlock.title"
            class="lobien-sidebar-content-block__title"
        >
            {{ contentBlock.title }}
        </h2>

        <div
            v-if="rows.length"
            class="lobien-sidebar-content-block__body"
        >
            <div
                v-for="(row, rowIndex) in rows"
                :key="rowIndex"
                class="content-block__item"
            >
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
    </section>
</template>

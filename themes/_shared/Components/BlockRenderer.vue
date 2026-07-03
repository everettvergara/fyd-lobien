<script setup>
import { computed } from 'vue';
import { resolveBlockComponent } from '../resolveBlockComponent.js';

const props = defineProps({
    type: { type: String, required: true },
    component: { type: String, default: null },
    blockProps: { type: Object, default: () => ({}) },
});

const BlockComponent = computed(() => {
    if (!props.component) {
        return null;
    }

    return resolveBlockComponent(props.component);
});
</script>

<template>
    <component
        :is="BlockComponent"
        v-if="BlockComponent"
        v-bind="{ ...blockProps, type }"
        :class="['page-block', `page-block-${type}`]"
    />
</template>

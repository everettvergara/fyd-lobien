<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    items: { type: Array, default: () => [] },
});
</script>

<template>
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><Link href="/">Home</Link></li>
            <li v-for="(item, index) in items" :key="index" class="breadcrumb-item" :class="{ active: index === items.length - 1 }">
                <Link v-if="item.url && index < items.length - 1" :href="item.url">{{ item.label }}</Link>
                <span v-else>{{ item.label }}</span>
            </li>
        </ol>
    </nav>
</template>

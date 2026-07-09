<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, default: '' },
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <div>
        <h6 v-if="title" class="lobien-footer-group-title">{{ title }}</h6>
        <ul>
            <li v-for="(link, index) in links" :key="index">
                <Link v-if="!link.url.startsWith('http') && link.url !== '#'" :href="link.url">{{ link.title }}</Link>
                <a v-else-if="link.url.startsWith('http')" :href="link.url" target="_blank" rel="noopener noreferrer">{{ link.title }}</a>
                <a v-else href="#" @click.prevent>{{ link.title }}</a>
            </li>
        </ul>
    </div>
</template>

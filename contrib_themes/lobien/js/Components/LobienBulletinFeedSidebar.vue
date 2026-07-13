<script setup>
import { Link } from '@inertiajs/vue3';

const links = [
    { title: 'Articles', url: '/articles' },
    { title: 'Videos', url: '/videos' },
    { title: 'Property tours', url: '/property-tours' },
    { title: 'Downloadables', url: '/downloadable' },
];
</script>

<template>
    <section
        class="lobien-sidebar-content-block content-block content-block--lrg-bulletin-options"
        id="content-block-lrg-bulletin-options"
    >
        <h2 class="lobien-sidebar-content-block__title">LRG Bulletin</h2>
        <div class="lobien-sidebar-content-block__body">
            <ul>
                <li v-for="link in links" :key="link.url">
                    <Link :href="link.url">{{ link.title }}</Link>
                </li>
            </ul>
        </div>
    </section>
</template>

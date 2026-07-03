<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <ul class="navbar-nav ms-auto align-items-lg-center">
        <template v-for="(item, index) in items" :key="index">
            <li v-if="!item.children?.length" class="nav-item">
                <Link
                    v-if="!item.url.startsWith('http')"
                    :href="item.url"
                    class="nav-link"
                >{{ item.title }}</Link>
                <a
                    v-else
                    :href="item.url"
                    class="nav-link"
                    :target="item.target"
                >{{ item.title }}</a>
            </li>
            <li v-else class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    {{ item.title }}
                </a>
                <ul class="dropdown-menu">
                    <li v-for="(child, ci) in item.children" :key="ci">
                        <Link v-if="!child.url.startsWith('http')" :href="child.url" class="dropdown-item">{{ child.title }}</Link>
                        <a v-else :href="child.url" class="dropdown-item" :target="child.target">{{ child.title }}</a>
                    </li>
                </ul>
            </li>
        </template>
        <li class="nav-item ms-lg-2">
            <Link href="/search" class="nav-link"><i class="bi bi-search"></i></Link>
        </li>
    </ul>
</template>

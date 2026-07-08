<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const currentPath = computed(() => {
    const url = page.url ?? '/';

    return url.split('?')[0].replace(/\/$/, '') || '/';
});

function isCtaItem(item) {
    const title = (item.title ?? '').toLowerCase().trim();

    return title === 'list your property'
        || title.includes('list your property');
}

function isActiveItem(item) {
    const itemPath = (item.url ?? '/').split('?')[0].replace(/\/$/, '') || '/';

    if (itemPath === '/') {
        return currentPath.value === '/';
    }

    return currentPath.value === itemPath
        || currentPath.value.startsWith(`${itemPath}/`);
}
</script>

<template>
    <ul class="navbar-nav lobien-nav ms-lg-auto align-items-lg-center">
        <template v-for="(item, index) in items" :key="index">
            <li
                v-if="!item.children?.length"
                class="nav-item"
                :class="{ 'nav-item-cta': isCtaItem(item) }"
            >
                <Link
                    v-if="!item.url.startsWith('http')"
                    :href="item.url"
                    class="nav-link"
                    :class="{ active: isActiveItem(item) }"
                >{{ item.title }}</Link>
                <a
                    v-else
                    :href="item.url"
                    class="nav-link"
                    :class="{ active: isActiveItem(item) }"
                    :target="item.target"
                    rel="noopener noreferrer"
                >{{ item.title }}</a>
            </li>
            <li v-else class="nav-item dropdown">
                <a
                    class="nav-link dropdown-toggle"
                    href="#"
                    role="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    {{ item.title }}
                </a>
                <ul class="dropdown-menu">
                    <li v-for="(child, ci) in item.children" :key="ci">
                        <Link
                            v-if="!child.url.startsWith('http')"
                            :href="child.url"
                            class="dropdown-item"
                            :class="{ active: isActiveItem(child) }"
                        >{{ child.title }}</Link>
                        <a
                            v-else
                            :href="child.url"
                            class="dropdown-item"
                            :target="child.target"
                            rel="noopener noreferrer"
                        >{{ child.title }}</a>
                    </li>
                </ul>
            </li>
        </template>
    </ul>
</template>

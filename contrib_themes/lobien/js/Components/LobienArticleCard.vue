<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    content: { type: Object, required: true },
});

const metaLine = computed(() => {
    const parts = [];

    if (props.content.publishedAt) {
        parts.push(props.content.publishedAt);
    }

    if (props.content.author) {
        parts.push(props.content.author);
    }

    return parts.join(' | ');
});

const readMoreHref = computed(() => (
    props.content.titleHref
    ?? props.content.imageHref
    ?? (props.content.path ? `/${props.content.path}` : null)
    ?? props.content.urlLink
    ?? null
));

function isExternalHref(href) {
    return typeof href === 'string' && /^https?:\/\//i.test(href);
}
</script>

<template>
    <article class="lobien-article-card">
        <div v-if="content.featuredImage" class="lobien-article-image">
            <Link
                v-if="content.imageHref && !isExternalHref(content.imageHref)"
                :href="content.imageHref"
                class="lobien-article-image-link"
            >
                <img
                    :src="content.featuredImage.url"
                    :alt="content.featuredImage.alt || content.title"
                    loading="lazy"
                >
            </Link>
            <a
                v-else-if="content.imageHref && isExternalHref(content.imageHref)"
                :href="content.imageHref"
                class="lobien-article-image-link"
                target="_blank"
                rel="noopener noreferrer"
            >
                <img
                    :src="content.featuredImage.url"
                    :alt="content.featuredImage.alt || content.title"
                    loading="lazy"
                >
            </a>
            <img
                v-else
                :src="content.featuredImage.url"
                :alt="content.featuredImage.alt || content.title"
                loading="lazy"
            >
        </div>

        <h3 class="lobien-article-title">
            <Link
                v-if="content.titleHref && !isExternalHref(content.titleHref)"
                :href="content.titleHref"
                class="lobien-article-title-link"
            >{{ content.title }}</Link>
            <a
                v-else-if="content.titleHref && isExternalHref(content.titleHref)"
                :href="content.titleHref"
                class="lobien-article-title-link"
                target="_blank"
                rel="noopener noreferrer"
            >{{ content.title }}</a>
            <template v-else>{{ content.title }}</template>
        </h3>

        <p v-if="metaLine" class="lobien-article-meta">{{ metaLine }}</p>
        <p v-if="content.summary" class="lobien-article-summary">{{ content.summary }}</p>

        <Link
            v-if="readMoreHref && !isExternalHref(readMoreHref)"
            :href="readMoreHref"
            class="lobien-article-link"
        >Read more</Link>
        <a
            v-else-if="readMoreHref && isExternalHref(readMoreHref)"
            :href="readMoreHref"
            class="lobien-article-link"
            target="_blank"
            rel="noopener noreferrer"
        >Read more</a>
    </article>
</template>

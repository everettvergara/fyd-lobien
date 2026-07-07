<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    cell: { type: Object, required: true },
    showLabel: { type: Boolean, default: false },
});

function fieldComponent(field) {
    if (field.linkToContent && field.contentPath) {
        if (field.field === 'featured_image' && field.value && typeof field.value === 'object') {
            return 'contentLinkImage';
        }

        if (field.field === 'attachment' && field.value && typeof field.value === 'object') {
            return 'contentLinkFile';
        }

        if (field.field === 'body') {
            return 'contentLinkHtml';
        }

        return 'contentLink';
    }

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

const component = () => fieldComponent(props.cell);
const contentHref = () => `/${props.cell.contentPath}`;
</script>

<template>
    <span v-if="showLabel" class="content-block__field-label">{{ cell.label }}: </span>

    <Link
        v-if="component() === 'contentLinkImage'"
        :href="contentHref()"
        class="content-block__content-link"
    >
        <img
            :src="cell.value.url"
            :srcset="cell.value.srcset || undefined"
            :sizes="cell.value.sizes || undefined"
            :alt="cell.value.alt"
            :width="cell.value.width || undefined"
            :height="cell.value.height || undefined"
            class="content-block__image"
            loading="lazy"
        >
    </Link>
    <Link
        v-else-if="component() === 'contentLinkFile'"
        :href="contentHref()"
        class="content-block__content-link content-block__file"
    >{{ cell.value.label }}</Link>
    <Link
        v-else-if="component() === 'contentLinkHtml'"
        :href="contentHref()"
        class="content-block__content-link"
    >
        <span v-html="cell.value" />
    </Link>
    <Link
        v-else-if="component() === 'contentLink'"
        :href="contentHref()"
        class="content-block__content-link"
    >{{ cell.value }}</Link>

    <img
        v-else-if="component() === 'image'"
        :src="cell.value.url"
        :srcset="cell.value.srcset || undefined"
        :sizes="cell.value.sizes || undefined"
        :alt="cell.value.alt"
        :width="cell.value.width || undefined"
        :height="cell.value.height || undefined"
        class="content-block__image"
        loading="lazy"
    >
    <a
        v-else-if="component() === 'link'"
        :href="cell.value"
        target="_blank"
        rel="noopener noreferrer"
        class="content-block__link"
    >{{ cell.value }}</a>
    <a
        v-else-if="component() === 'file'"
        :href="cell.value.url"
        target="_blank"
        rel="noopener noreferrer"
        class="content-block__file"
    >{{ cell.value.label }}</a>
    <span v-else-if="component() === 'html'" v-html="cell.value" />
    <span v-else>{{ cell.value }}</span>
</template>

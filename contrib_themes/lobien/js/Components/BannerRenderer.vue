<script setup>
import { computed } from 'vue';

const props = defineProps({
    banner: { type: Object, required: true },
    slide: { type: Object, default: null },
});

const activeSlide = computed(() => props.slide ?? props.banner.slides?.[0] ?? {});
const block = computed(() => activeSlide.value.blocks?.[0] ?? {});
const media = computed(() => activeSlide.value.media ?? {});
const templateKey = computed(() => props.banner.template?.key ?? 'hero_center');

const isColumnLayout = computed(() => [
    'two_column_full_width',
    'three_column_full_width',
    'four_column_full_width',
].includes(templateKey.value));

const columns = computed(() => {
    if (! isColumnLayout.value) {
        return [];
    }

    return (activeSlide.value.blocks ?? []).map((columnBlock) => {
        const slot = columnBlock.region ? `${columnBlock.region}_image` : null;
        const image = slot ? media.value[slot] : null;

        return {
            ...columnBlock,
            image,
            buttons: columnBlock.buttons?.length
                ? columnBlock.buttons
                : [],
        };
    });
});

const columnClass = computed(() => {
    if (templateKey.value === 'four_column_full_width') {
        return 'col-lg-3 col-md-6';
    }

    if (templateKey.value === 'three_column_full_width') {
        return 'col-md-4';
    }

    return 'col-md-6';
});

const headline = computed(() => block.value.headline ?? props.banner.title);
const subheading = computed(() => block.value.subheading ?? props.banner.subtitle);
const description = computed(() => block.value.description ?? props.banner.description);
const richText = computed(() => block.value.richText ?? '');
const buttons = computed(() => block.value.buttons?.length
    ? block.value.buttons
    : props.banner.buttonText
        ? [{ label: props.banner.buttonText, url: props.banner.buttonUrl ?? '#', target: '_self', style: 'primary' }]
        : []);

const desktopImage = computed(() => media.value.desktop_image ?? media.value.background_image ?? props.banner.desktopImage ?? props.banner.backgroundImage);
const mobileImage = computed(() => media.value.mobile_image ?? props.banner.mobileImage);
const video = computed(() => media.value.background_video);
const poster = computed(() => media.value.poster_image ?? desktopImage.value);

const alignmentClass = computed(() => ({
    hero_left: 'text-start',
    image_left: 'text-start',
    image_right: 'text-start',
    hero_right: 'text-end ms-auto',
}[templateKey.value] ?? 'text-center mx-auto'));

const isSplit = computed(() => ['split_layout', 'image_left', 'image_right'].includes(templateKey.value));
const isInnerPage = computed(() => templateKey.value === 'inner_page');
const isFullBleedImage = computed(() => props.banner.key === 'about-footer-banner');
const isMinimalSection = computed(() => templateKey.value === 'minimal');

const columnSectionHeadline = computed(() => {
    if (templateKey.value !== 'two_column_full_width') {
        return null;
    }

    return columns.value.find((column) => column.headline)?.headline ?? null;
});

const bannerKeyClass = computed(() => {
    const key = props.banner.key;
    return key ? `public-banner-key-${key}` : null;
});

const splitRowAlignClass = computed(() => {
    const key = props.banner.key ?? '';
    if (['about-person-sheila', 'about-person-alex', 'about-person-steph'].includes(key)) {
        return 'align-items-start';
    }

    return 'align-items-center';
});

const headlineClass = computed(() => {
    if (isSplit.value || isMinimalSection.value) {
        return 'public-banner-split-title';
    }

    return 'display-4 fw-bold mb-3';
});
</script>

<template>
    <section
        v-if="isInnerPage"
        class="public-banner public-banner-inner_page position-relative overflow-hidden"
        :aria-label="props.banner.name || headline"
    >
        <picture v-if="desktopImage">
            <img class="public-banner-media" :src="desktopImage.url" :alt="desktopImage.alt || ''" loading="lazy">
        </picture>
        <div v-if="desktopImage" class="public-banner-overlay"></div>
        <div class="container position-relative">
            <div class="public-banner-inner_page-content text-center mx-auto">
                <p v-if="subheading" class="text-uppercase fw-semibold mb-1 opacity-75 small">{{ subheading }}</p>
                <h1 class="h2 fw-bold mb-2">{{ headline }}</h1>
                <p v-if="description" class="mb-0 opacity-90">{{ description }}</p>
            </div>
        </div>
    </section>

    <section
        v-else-if="isColumnLayout"
        class="public-banner public-banner-columns"
        :class="[`public-banner-${templateKey}`, bannerKeyClass]"
        :aria-label="props.banner.name || 'Banner columns'"
    >
        <div class="container py-5">
            <h2 v-if="columnSectionHeadline" class="public-banner-split-title">{{ columnSectionHeadline }}</h2>
            <div class="row g-4">
                <div
                    v-for="(column, index) in columns"
                    :key="column.id || column.region || index"
                    :class="columnClass"
                >
                    <article class="public-banner-column h-100">
                        <img
                            v-if="column.image"
                            :src="column.image.url"
                            :alt="column.image.alt || column.headline || ''"
                            class="public-banner-column-image img-fluid rounded mb-3"
                            loading="lazy"
                        >
                        <div class="public-banner-column-content">
                            <h2
                                v-if="column.headline && templateKey !== 'two_column_full_width'"
                                class="public-banner-split-title"
                            >{{ column.headline }}</h2>
                            <p v-if="column.subheading" class="text-uppercase small fw-semibold text-muted mb-2">{{ column.subheading }}</p>
                            <p v-if="column.description" class="mb-3">{{ column.description }}</p>
                            <div v-if="column.buttons.length" class="d-flex flex-wrap gap-2">
                                <a
                                    v-for="button in column.buttons"
                                    :key="button.id || button.label"
                                    :href="button.url"
                                    :target="button.target || '_self'"
                                    class="btn btn-sm"
                                    :class="`btn-${button.style || 'primary'}`"
                                >
                                    {{ button.label }}
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section
        v-else-if="isFullBleedImage"
        class="public-banner public-banner-full-bleed"
        :class="bannerKeyClass"
        :aria-label="props.banner.name || 'Banner'"
    >
        <img
            v-if="desktopImage"
            class="public-banner-full-bleed-image"
            :src="desktopImage.url"
            :alt="desktopImage.alt || ''"
            loading="lazy"
        >
    </section>

    <section
        v-else
        class="public-banner position-relative overflow-hidden"
        :class="[`public-banner-${templateKey}`, bannerKeyClass, { 'public-banner-split': isSplit }]"
        :aria-label="props.banner.name || headline"
    >
        <video
            v-if="video"
            class="public-banner-media"
            :src="video.url"
            :poster="poster?.url"
            autoplay
            muted
            loop
            playsinline
        />
        <picture v-else-if="desktopImage">
            <source v-if="mobileImage" media="(max-width: 767px)" :srcset="mobileImage.url">
            <img class="public-banner-media" :src="desktopImage.url" :alt="desktopImage.alt || ''" loading="lazy">
        </picture>

        <div class="public-banner-overlay"></div>
        <div class="container position-relative pt-5">
            <div class="row g-4" :class="splitRowAlignClass">
                <div class="col-lg" :class="{ 'order-lg-2': templateKey === 'image_left' }">
                    <div class="public-banner-content" :class="[alignmentClass, { 'public-banner-content--split': isSplit }]">
                        <p v-if="subheading" class="text-uppercase fw-semibold mb-2 opacity-75 small">{{ subheading }}</p>
                        <h1 :class="headlineClass">{{ headline }}</h1>
                        <p v-if="description" :class="isSplit ? 'public-banner-split-desc' : 'lead mb-3 opacity-90'">{{ description }}</p>
                        <div v-if="richText" class="public-banner-rich-text mb-4" v-html="richText"></div>
                        <div v-if="buttons.length" class="d-flex flex-wrap gap-2" :class="{ 'justify-content-center': alignmentClass.includes('text-center'), 'justify-content-end': alignmentClass.includes('text-end') }">
                            <a
                                v-for="button in buttons"
                                :key="button.id || button.label"
                                :href="button.url"
                                :target="button.target || '_self'"
                                class="btn"
                                :class="`btn-${button.style || 'primary'}`"
                            >
                                {{ button.label }}
                            </a>
                        </div>
                    </div>
                </div>
                <div v-if="isSplit && desktopImage" class="col-lg">
                    <img :src="desktopImage.url" :alt="desktopImage.alt || ''" class="img-fluid public-banner-split-image" loading="lazy">
                </div>
            </div>
        </div>
    </section>
</template>

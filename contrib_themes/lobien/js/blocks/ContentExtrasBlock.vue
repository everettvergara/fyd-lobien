<script setup>
import { computed } from 'vue';
import PageContentShell from '../../../_shared/Components/PageContentShell.vue';
import { youtubeEmbedUrl } from '../utils/youtube.js';

const props = defineProps({
    urlLink: { type: String, default: '' },
    attachment: { type: Object, default: null },
    galleryImages: { type: Array, default: () => [] },
});

const youtubeSrc = computed(() => youtubeEmbedUrl(props.urlLink));
const hasContent = computed(() => (
    Boolean(props.urlLink || props.attachment || props.galleryImages.length)
));
</script>

<template>
    <PageContentShell
        v-if="hasContent"
        spacing="section"
    >
        <section class="content-extras-block">
            <div
                v-if="youtubeSrc"
                class="content-extras-block__video lobien-article-video mb-4"
            >
                <iframe
                    :src="youtubeSrc"
                    title="YouTube video"
                    loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                    referrerpolicy="strict-origin-when-cross-origin"
                />
            </div>
            <p
                v-else-if="urlLink"
                class="content-extras-block__link mb-4"
            >
                <a
                    :href="urlLink"
                    class="btn btn-outline-primary"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Visit link
                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
            </p>

            <p
                v-if="attachment"
                class="content-extras-block__attachment mb-4"
            >
                <a
                    :href="attachment.url"
                    class="content-extras-block__file"
                    download
                >
                    {{ attachment.label }}
                </a>
            </p>

            <div
                v-if="galleryImages.length"
                class="content-extras-block__gallery row g-3"
            >
                <div
                    v-for="(image, index) in galleryImages"
                    :key="index"
                    class="col-md-6 col-lg-4"
                >
                    <img
                        :src="image.url"
                        :srcset="image.srcset || undefined"
                        :sizes="image.sizes || undefined"
                        :alt="image.alt"
                        :width="image.width || undefined"
                        :height="image.height || undefined"
                        class="img-fluid rounded w-100"
                        loading="lazy"
                    >
                </div>
            </div>
        </section>
    </PageContentShell>
</template>

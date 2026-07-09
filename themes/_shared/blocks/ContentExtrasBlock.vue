<script setup>
import PageContentShell from '../Components/PageContentShell.vue';

defineProps({
    urlLink: { type: String, default: '' },
    attachment: { type: Object, default: null },
    galleryImages: { type: Array, default: () => [] },
});
</script>

<template>
    <PageContentShell
        v-if="urlLink || attachment || galleryImages.length"
        spacing="section"
    >
        <section class="content-extras-block">
            <p v-if="urlLink" class="content-extras-block__link mb-4">
                <a :href="urlLink" class="btn btn-outline-primary" target="_blank" rel="noopener noreferrer">
                    Visit link
                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
            </p>

            <p v-if="attachment" class="content-extras-block__attachment mb-4">
                <a :href="attachment.url" class="content-extras-block__file" download>
                    {{ attachment.label }}
                </a>
            </p>

            <div v-if="galleryImages.length" class="content-extras-block__gallery row g-3">
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

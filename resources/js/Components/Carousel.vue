<script setup>
import BannerRenderer from '@/Components/BannerRenderer.vue';

defineProps({
    banner: { type: Object, required: true },
});
</script>

<template>
    <div id="homeCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div
                v-for="(slide, index) in banner.slides"
                :key="slide.id || index"
                class="carousel-item"
                :class="{ active: index === 0 }"
            >
                <BannerRenderer :banner="banner" :slide="slide" />
            </div>
        </div>
        <button v-if="banner.slides.length > 1" class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button v-if="banner.slides.length > 1" class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</template>

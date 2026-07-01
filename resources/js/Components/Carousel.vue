<script setup>
import HeroBanner from '@/Components/HeroBanner.vue';

defineProps({
    banners: { type: Array, required: true },
});
</script>

<template>
    <div id="homeCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div
                v-for="(banner, index) in banners"
                :key="index"
                class="carousel-item"
                :class="{ active: index === 0 }"
            >
                <HeroBanner
                    :title="banner.title"
                    :subtitle="banner.subtitle"
                    :description="banner.description"
                    :button-text="banner.buttonText"
                    :button-url="banner.buttonUrl"
                    :desktop-image="banner.desktopImage"
                />
            </div>
        </div>
        <button v-if="banners.length > 1" class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button v-if="banners.length > 1" class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    description: { type: String, default: '' },
    buttonText: { type: String, default: '' },
    buttonUrl: { type: String, default: '#' },
    desktopImage: { type: Object, default: null },
});
</script>

<template>
    <section
        class="public-hero"
        :style="desktopImage ? { backgroundImage: `linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url(${desktopImage.url})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}"
    >
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <p v-if="subtitle" class="text-uppercase fw-semibold mb-2 opacity-75 small">{{ subtitle }}</p>
                    <h1 class="display-4 fw-bold mb-3">{{ title }}</h1>
                    <p v-if="description" class="lead mb-4 opacity-90">{{ description }}</p>
                    <a v-if="buttonText" :href="buttonUrl" class="btn btn-primary btn-lg">{{ buttonText }}</a>
                </div>
            </div>
        </div>
    </section>
</template>

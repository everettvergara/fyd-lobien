<script setup>
defineProps({
    section: { type: Object, required: true },
});
</script>

<template>
    <section class="public-section" :class="{ 'public-section-alt': section.type === 'feature_grid' }">
        <div class="container">
            <div v-if="section.settings?.title" class="text-center mb-5">
                <h2 class="fw-bold">{{ section.settings.title }}</h2>
                <p v-if="section.settings.subtitle" class="text-muted">{{ section.settings.subtitle }}</p>
            </div>

            <div v-if="section.type === 'hero_banner'" class="text-center py-4">
                <h2>{{ section.settings.title }}</h2>
                <p class="lead">{{ section.settings.subtitle }}</p>
            </div>

            <div v-else-if="section.type === 'cta'" class="text-center py-4">
                <h3 class="fw-bold mb-3">{{ section.settings.title || 'Get Started' }}</h3>
                <a v-if="section.settings.button_url" :href="section.settings.button_url" class="btn btn-primary btn-lg">
                    {{ section.settings.button_text || 'Contact Us' }}
                </a>
            </div>

            <div v-else-if="section.type === 'contact'" class="row justify-content-center">
                <div class="col-lg-6 text-center">
                    <h3 class="fw-bold mb-3">{{ section.settings.title || 'Contact Us' }}</h3>
                    <p class="text-muted">{{ section.settings.subtitle || 'Get in touch with our team.' }}</p>
                </div>
            </div>

            <div v-else class="text-center text-muted py-3">
                <span class="badge bg-secondary">{{ section.type.replace('_', ' ') }}</span>
            </div>
        </div>
    </section>
</template>

<script setup>
import WebformRenderer from '../Components/WebformRenderer.vue';

defineProps({
    slug: { type: String, default: '' },
});
</script>

<template>
    <section v-if="slug" class="lobien-contact-form lobien-section">
        <div class="lobien-contact-form__header">
            <h2 class="lobien-contact-form__title">Send us a message</h2>
            <p class="lobien-contact-form__subtitle">
                <em>For general inquiries, fill out the details below:</em>
            </p>
        </div>
        <WebformRenderer :slug="slug" />
    </section>
</template>

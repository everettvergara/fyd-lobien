<script setup>
defineProps({
    embed_url: { type: String, required: true },
    title: { type: String, default: 'Office location' },
});
</script>

<template>
    <section class="lobien-contact-map">
        <div class="lobien-contact-map__frame">
            <iframe
                :src="embed_url"
                :title="title"
                style="border:0;"
                allowfullscreen
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
            />
        </div>
    </section>
</template>

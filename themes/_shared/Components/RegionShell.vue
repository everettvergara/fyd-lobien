<script setup>
defineProps({
    region: { type: String, required: true },
});
</script>

<template>
    <section :class="['page-region', `page-region--${region}`]">
        <slot />
    </section>
</template>

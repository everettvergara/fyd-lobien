<script setup>
defineProps({
    type: { type: String, default: 'unknown' },
});
</script>

<template>
    <div class="alert alert-warning small">Unknown block: {{ type }}</div>
</template>

<script setup>
defineProps({
    width: { type: String, default: 'content' },
    spacing: { type: String, default: 'default' },
});
</script>

<template>
    <div
        class="page-content-shell"
        :class="[
            `page-content-shell--${width}`,
            spacing !== 'default' ? `page-content-shell--spacing-${spacing}` : '',
        ]"
    >
        <div class="container">
            <div class="row justify-content-center">
                <div :class="width === 'narrow' ? 'col-lg-8' : 'col-lg-10 col-xl-9'">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>

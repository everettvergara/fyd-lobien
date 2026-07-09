<script setup>
import LobienWhatsNewSection from '../Components/LobienWhatsNewSection.vue';
import SharedContentBlockBlock from '../../../_shared/blocks/ContentBlockBlock.vue';

defineProps({
    contentBlock: { type: Object, default: null },
});

function isArticleGridBlock(block) {
    if (!block?.rows?.length) {
        return false;
    }

    return block.key === 'latest-articles'
        || block.wrapperClass?.includes('latest-articles');
}
</script>

<template>
    <LobienWhatsNewSection
        v-if="isArticleGridBlock(contentBlock)"
        :content-block="contentBlock"
    />
    <SharedContentBlockBlock
        v-else
        :content-block="contentBlock"
    />
</template>

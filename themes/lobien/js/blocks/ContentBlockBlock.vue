<script setup>
import LobienWhatsNewSection from '../Components/LobienWhatsNewSection.vue';
import LobienBulletinListingSection from '../Components/LobienBulletinListingSection.vue';
import LobienSidebarContentBlock from '../Components/LobienSidebarContentBlock.vue';
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

function isBulletinListingBlock(block) {
    return block?.key === 'lrg-bulletin-feeds'
        || block?.wrapperClass?.includes('lrg-bulletin-feeds');
}

function isBulletinSidebarBlock(block) {
    return block?.key === 'lrg-bulletin-options'
        || block?.wrapperClass?.includes('lrg-bulletin-options');
}
</script>

<template>
    <LobienWhatsNewSection
        v-if="isArticleGridBlock(contentBlock)"
        :content-block="contentBlock"
    />
    <LobienBulletinListingSection
        v-else-if="isBulletinListingBlock(contentBlock)"
        :content-block="contentBlock"
    />
    <LobienSidebarContentBlock
        v-else-if="isBulletinSidebarBlock(contentBlock)"
        :content-block="contentBlock"
    />
    <SharedContentBlockBlock
        v-else
        :content-block="contentBlock"
    />
</template>

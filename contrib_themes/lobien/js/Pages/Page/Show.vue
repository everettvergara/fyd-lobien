<script setup>
import { computed } from 'vue';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SeoHead from '../../Components/SeoHead.vue';
import Breadcrumb from '../../Components/Breadcrumb.vue';
import LobienBulletinFeedSidebar from '../../Components/LobienBulletinFeedSidebar.vue';
import RegionShell from '../../../../_shared/Components/RegionShell.vue';
import BlockRenderer from '../../../../_shared/Components/BlockRenderer.vue';

const FEED_LISTING_PATHS = [
    '/articles',
    '/downloadable',
    '/videos',
    '/property-tours',
    '/social-media',
];

const props = defineProps({
    page: { type: Object, required: true },
    regionOrder: { type: Array, default: () => [] },
    regions: { type: Object, default: () => ({}) },
    seo: { type: Object, default: () => ({}) },
});

const hasSidebar = computed(() => (props.regions.sidebar || []).length > 0);
const isFeedListingPath = computed(() => FEED_LISTING_PATHS.includes(props.page.path));
const isSidebarLayout = computed(() => hasSidebar.value || isFeedListingPath.value);
const useThemeFeedSidebar = computed(() => isFeedListingPath.value && !hasSidebar.value);

const breadcrumbItems = computed(() => [{ label: props.page.title }]);

function blocksFor(region) {
    return props.regions[region] || [];
}

const mainBlocks = computed(() => blocksFor('main'));

const leadingMainBanners = computed(() => {
    const out = [];
    for (const block of mainBlocks.value) {
        if (block.type !== 'banner') {
            break;
        }
        out.push(block);
    }
    return out;
});

const sidebarMainBlocks = computed(() =>
    mainBlocks.value.slice(leadingMainBanners.value.length)
);
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="seo" />

        <RegionShell v-if="blocksFor('hero').length" region="hero">
            <BlockRenderer
                v-for="block in blocksFor('hero')"
                :key="block.id"
                :type="block.type"
                :component="block.component"
                :block-props="block.props"
            />
        </RegionShell>

        <template v-if="isSidebarLayout">
            <div class="lobien-container pt-4">
                <Breadcrumb :items="breadcrumbItems" />
            </div>

            <BlockRenderer
                v-for="block in leadingMainBanners"
                :key="block.id"
                :type="block.type"
                :component="block.component"
                :block-props="block.props"
            />

            <div class="lobien-page-with-sidebar lobien-container">
                <div class="lobien-page-with-sidebar__main">
                    <BlockRenderer
                        v-for="block in sidebarMainBlocks"
                        :key="block.id"
                        :type="block.type"
                        :component="block.component"
                        :block-props="block.props"
                    />
                </div>
                <aside class="lobien-page-with-sidebar__sidebar page-region--sidebar">
                    <BlockRenderer
                        v-for="block in blocksFor('sidebar')"
                        :key="block.id"
                        :type="block.type"
                        :component="block.component"
                        :block-props="block.props"
                    />
                    <LobienBulletinFeedSidebar v-if="useThemeFeedSidebar" />
                </aside>
            </div>
        </template>

        <template v-else>
            <RegionShell
                v-for="region in regionOrder.filter((r) => r !== 'hero' && r !== 'footer')"
                :key="region"
                :region="region"
            >
                <div
                    v-if="region === 'main' && page.path !== '/'"
                    class="lobien-container pt-4"
                >
                    <Breadcrumb :items="breadcrumbItems" />
                </div>

                <BlockRenderer
                    v-for="block in blocksFor(region)"
                    :key="block.id"
                    :type="block.type"
                    :component="block.component"
                    :block-props="block.props"
                />
            </RegionShell>
        </template>

        <RegionShell v-if="blocksFor('footer').length" region="footer">
            <BlockRenderer
                v-for="block in blocksFor('footer')"
                :key="block.id"
                :type="block.type"
                :component="block.component"
                :block-props="block.props"
            />
        </RegionShell>
    </PublicLayout>
</template>

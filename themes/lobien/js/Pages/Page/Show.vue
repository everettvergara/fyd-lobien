<script setup>
import { computed } from 'vue';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SeoHead from '../../Components/SeoHead.vue';
import Breadcrumb from '../../Components/Breadcrumb.vue';
import RegionShell from '../../../../_shared/Components/RegionShell.vue';
import BlockRenderer from '../../../../_shared/Components/BlockRenderer.vue';

const props = defineProps({
    page: { type: Object, required: true },
    regionOrder: { type: Array, default: () => [] },
    regions: { type: Object, default: () => ({}) },
    seo: { type: Object, default: () => ({}) },
});

const hasSidebar = computed(() => (props.regions.sidebar || []).length > 0);
const isSidebarLayout = computed(() => hasSidebar.value);

const breadcrumbItems = computed(() => [{ label: props.page.title }]);

function blocksFor(region) {
    return props.regions[region] || [];
}
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
            <div class="lobien-page-with-sidebar lobien-container pt-4">
                <div class="lobien-page-with-sidebar__main">
                    <Breadcrumb :items="breadcrumbItems" />
                    <BlockRenderer
                        v-for="block in blocksFor('main')"
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

<script setup>
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SeoHead from '../../Components/SeoHead.vue';
import Breadcrumb from '../../Components/Breadcrumb.vue';
import RegionShell from '../../../../_shared/Components/RegionShell.vue';
import BlockRenderer from '../../../../_shared/Components/BlockRenderer.vue';

defineProps({
    page: { type: Object, required: true },
    regionOrder: { type: Array, default: () => [] },
    regions: { type: Object, default: () => ({}) },
    seo: { type: Object, default: () => ({}) },
});
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="seo" />

        <RegionShell
            v-for="region in regionOrder"
            :key="region"
            :region="region"
        >
            <div
                v-if="region === 'main' && page.path !== '/'"
                class="page-breadcrumb-bar"
            >
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-10 col-xl-9">
                            <Breadcrumb :items="[{ label: page.title }]" />
                        </div>
                    </div>
                </div>
            </div>

            <BlockRenderer
                v-for="block in (regions[region] || [])"
                :key="block.id"
                :type="block.type"
                :component="block.component"
                :block-props="block.props"
            />
        </RegionShell>
    </PublicLayout>
</template>

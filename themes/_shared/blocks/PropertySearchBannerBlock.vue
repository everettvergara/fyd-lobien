<script setup>
import PropertySearchFilterForm from '../Components/PropertySearchFilterForm.vue';

defineProps({
    heading: { type: String, default: 'Find your property' },
    background_image_url: { type: String, default: null },
    background_image_alt: { type: String, default: '' },
    action_url: { type: String, required: true },
    cities: { type: Array, default: () => [] },
    property_types: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});
</script>

<template>
    <section class="property-search-banner position-relative text-white"
             :style="background_image_url
                 ? { backgroundImage: `url('${background_image_url}')`, backgroundSize: 'cover', backgroundPosition: 'center' }
                 : {}"
             :class="{ 'bg-dark': !background_image_url }">
        <div class="property-search-banner__overlay position-absolute top-0 start-0 w-100 h-100"
             style="background: rgba(0, 0, 0, 0.5);"></div>
        <div class="container position-relative py-5">
            <div class="py-4">
                <h1 class="display-6 fw-bold mb-4">{{ heading }}</h1>
                <div class="bg-white rounded-3 shadow p-3 text-body">
                    <PropertySearchFilterForm
                        :action-url="action_url"
                        :cities="cities"
                        :property-types="property_types"
                        :filters="filters"
                        :show-city="true" />
                </div>
            </div>
        </div>
    </section>
</template>

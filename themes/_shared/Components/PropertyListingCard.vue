<script setup>
defineProps({
    listing: { type: Object, required: true },
});
</script>

<template>
    <article class="card h-100 border-0 shadow-sm property-listing-card">
        <a v-if="listing.url" :href="listing.url" class="text-decoration-none text-reset d-flex flex-column h-100">
            <img v-if="listing.building_image_urls?.[0]"
                 :src="listing.building_image_urls[0]"
                 :alt="listing.name"
                 class="card-img-top"
                 style="height: 200px; object-fit: cover;">
            <div v-else class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                <span class="text-muted small">No image</span>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="mb-2">
                    <span v-if="listing.for_lease" class="badge text-bg-primary me-1">For Lease</span>
                    <span v-if="listing.for_sale" class="badge text-bg-success">For Sale</span>
                </div>
                <h3 class="h5 card-title mb-1">{{ listing.name }}</h3>
                <p v-if="listing.developer" class="card-text text-muted small mb-1">
                    {{ listing.developer }}
                </p>
                <p v-if="listing.summary" class="card-text small mb-2 property-listing-card__teaser">
                    {{ listing.summary }}
                </p>
                <div class="mt-auto small text-muted">
                    <p v-if="listing.address || listing.city" class="mb-1">
                        <i class="bi bi-geo-alt me-1"></i>{{ listing.address || listing.city }}
                    </p>
                    <p v-if="listing.total_area_size" class="mb-0">
                        <i class="bi bi-arrows-fullscreen me-1"></i>{{ listing.total_area_size }} sqm
                    </p>
                </div>
            </div>
        </a>
    </article>
</template>

<style scoped>
.property-listing-card__teaser {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

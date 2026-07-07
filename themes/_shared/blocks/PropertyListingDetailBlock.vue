<script setup>
import PageContentShell from '../Components/PageContentShell.vue';

defineProps({
    listing: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <PageContentShell spacing="section">
        <section v-if="listing" class="property-listing-detail">
            <header class="mb-4">
                <p v-if="listing.city" class="text-muted small mb-1">{{ listing.city }}</p>
                <h1 class="h2 fw-bold mb-2">{{ listing.name }}</h1>
                <p v-if="listing.summary" class="lead mb-2">{{ listing.summary }}</p>
                <p v-if="listing.code" class="text-muted small mb-0">Code: {{ listing.code }}</p>
            </header>

            <div v-if="listing.building_image_urls?.length" class="row g-3 mb-4">
                <div v-for="(url, index) in listing.building_image_urls" :key="index" class="col-md-4">
                    <img :src="url" :alt="listing.name" class="img-fluid rounded shadow-sm">
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div v-if="listing.description" class="mb-4 property-listing-detail__description" v-html="listing.description" />

                    <dl class="row small mb-4">
                        <template v-if="listing.address">
                            <dt class="col-sm-4">Address</dt>
                            <dd class="col-sm-8">{{ listing.address }}</dd>
                        </template>
                        <template v-if="listing.completion_status">
                            <dt class="col-sm-4">Completion</dt>
                            <dd class="col-sm-8">{{ listing.completion_status }}</dd>
                        </template>
                        <template v-if="listing.office_rental_rate">
                            <dt class="col-sm-4">Office rental rate</dt>
                            <dd class="col-sm-8">{{ listing.office_rental_rate }}</dd>
                        </template>
                        <template v-if="listing.total_area_size">
                            <dt class="col-sm-4">Total area</dt>
                            <dd class="col-sm-8">{{ listing.total_area_size }}</dd>
                        </template>
                        <template v-if="listing.net_usable_area">
                            <dt class="col-sm-4">Net usable area</dt>
                            <dd class="col-sm-8">{{ listing.net_usable_area }}</dd>
                        </template>
                    </dl>

                    <section v-if="listing.units?.length" class="mb-4">
                        <h2 class="h4 fw-semibold mb-3">Units</h2>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Floor</th>
                                        <th>Unit</th>
                                        <th>Area</th>
                                        <th>Rental</th>
                                        <th>Availability</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="unit in listing.units" :key="unit.id">
                                        <td>{{ unit.floor }}</td>
                                        <td>{{ unit.unit }}</td>
                                        <td>{{ unit.area_size }}</td>
                                        <td>{{ unit.rental }}</td>
                                        <td>{{ unit.availability }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section v-if="listing.fees?.length" class="mb-4">
                        <h2 class="h4 fw-semibold mb-3">Fees</h2>
                        <ul class="list-unstyled mb-0">
                            <li v-for="fee in listing.fees" :key="fee.id" class="mb-1">
                                <strong>{{ fee.fee_type }}</strong>: {{ fee.fee }}
                            </li>
                        </ul>
                    </section>
                </div>

                <div class="col-lg-4">
                    <section v-if="listing.spec" class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h5 fw-semibold mb-3">Specifications</h2>
                            <dl class="row small mb-0">
                                <template v-if="listing.spec.developer">
                                    <dt class="col-5">Developer</dt>
                                    <dd class="col-7">{{ listing.spec.developer }}</dd>
                                </template>
                                <template v-if="listing.spec.grade">
                                    <dt class="col-5">Grade</dt>
                                    <dd class="col-7">{{ listing.spec.grade }}</dd>
                                </template>
                                <template v-if="listing.spec.no_of_floors">
                                    <dt class="col-5">Floors</dt>
                                    <dd class="col-7">{{ listing.spec.no_of_floors }}</dd>
                                </template>
                            </dl>
                        </div>
                    </section>

                    <section v-if="listing.building_service" class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h5 fw-semibold mb-3">Building services</h2>
                            <dl class="row small mb-0">
                                <template v-if="listing.building_service.ac_system">
                                    <dt class="col-5">AC</dt>
                                    <dd class="col-7">{{ listing.building_service.ac_system }}</dd>
                                </template>
                                <template v-if="listing.building_service.backup_power">
                                    <dt class="col-5">Backup power</dt>
                                    <dd class="col-7">{{ listing.building_service.backup_power }}</dd>
                                </template>
                            </dl>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </PageContentShell>
</template>

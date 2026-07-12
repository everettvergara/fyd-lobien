<script setup>
import { reactive } from 'vue';

const props = defineProps({
    actionUrl: { type: String, required: true },
    cities: { type: Array, default: () => [] },
    propertyTypes: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    showCity: { type: Boolean, default: true },
});

const form = reactive({
    city: props.filters?.city ?? 'all',
    property_type: props.filters?.property_type ?? 'all',
    intent: props.filters?.intent ?? 'all',
    name: props.filters?.name ?? '',
});
</script>

<template>
    <form :action="actionUrl" method="get" class="property-search-filter row g-2 align-items-end">
        <div v-if="showCity" class="col-md">
            <label class="form-label small mb-1">City</label>
            <select v-model="form.city" name="city" class="form-select">
                <option value="all">All cities</option>
                <option v-for="city in cities" :key="city.value" :value="city.value">
                    {{ city.label }}
                </option>
            </select>
        </div>
        <div class="col-md">
            <label class="form-label small mb-1">Property type</label>
            <select v-model="form.property_type" name="property_type" class="form-select">
                <option value="all">All types</option>
                <option v-for="type in propertyTypes" :key="type.value" :value="type.value">
                    {{ type.label }}
                </option>
            </select>
        </div>
        <div class="col-md">
            <label class="form-label small mb-1">Lease / Sale</label>
            <select v-model="form.intent" name="intent" class="form-select">
                <option value="all">Lease or Sale</option>
                <option value="lease">For Lease</option>
                <option value="sale">For Sale</option>
            </select>
        </div>
        <div class="col-md">
            <label class="form-label small mb-1">Property name</label>
            <input v-model="form.name"
                   type="text"
                   name="name"
                   class="form-control"
                   placeholder="Building name">
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-search">Search</button>
        </div>
    </form>
</template>

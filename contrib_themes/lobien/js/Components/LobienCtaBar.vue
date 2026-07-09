<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const contact = computed(() => page.props.app?.contact ?? {});
const phone = computed(() => contact.value.phone || '+632 8983 9311');
const email = computed(() => contact.value.email || 'inquiry@lobiengroup.com');
</script>

<template>
    <div class="lobien-cta-bar">
        <div class="lobien-container">
            <p>
                Get the best properties today. Call us at
                <a :href="`tel:${phone}`">{{ phone }}</a>
                or send us an email at
                <a :href="`mailto:${email}`">{{ email }}</a>
            </p>
        </div>
    </div>
</template>

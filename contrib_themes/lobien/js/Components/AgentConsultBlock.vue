<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const contact = page.props.app?.contact ?? {};

const sampleProperties = [
    '100 West Building', '111 Paseo de Roxas', 'One Bonifacio High Street',
    'Zuellig Building', 'Ayala Triangle Gardens Tower 2', 'RCBC Plaza',
    'Bonifacio High Street - Central', 'The Enterprise Center Tower I',
];
</script>

<template>
    <section class="lobien-consult-block">
        <div class="lobien-consult-inner">
            <div class="lobien-consult-col">
                <h2>Consult with an Agent</h2>
                <p><em>Speak directly with our property consultants. Select your preferred property or send us your general inquiry.</em></p>
            </div>

            <div class="lobien-consult-col">
                <label class="form-label small text-uppercase" for="lobien-property-select">I'm interested in...</label>
                <select id="lobien-property-select" class="form-select">
                    <option value="">- Select -</option>
                    <option v-for="name in sampleProperties" :key="name" :value="name">{{ name }}</option>
                </select>
            </div>

            <div class="lobien-consult-col">
                <h2 class="h5 text-uppercase">Or send us a message</h2>
                <a
                    :href="contact.email ? `mailto:${contact.email}` : '/contact-us'"
                    class="lobien-consult-link"
                >Contact us</a>
            </div>
        </div>
    </section>
</template>

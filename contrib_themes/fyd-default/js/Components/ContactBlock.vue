<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const contact = page.props.app?.contact ?? {};
</script>

<template>
    <section id="contact" class="public-section public-section-alt">
        <div class="container">
            <div class="row justify-content-center text-center mb-4">
                <div class="col-lg-8">
                    <h2 class="fw-bold">Contact Us</h2>
                    <p class="text-muted">We would love to hear from you.</p>
                </div>
            </div>
            <div class="row justify-content-center g-4">
                <div v-if="contact.email" class="col-md-4 text-center">
                    <i class="bi bi-envelope fs-2 text-primary mb-2"></i>
                    <h6>Email</h6>
                    <a :href="`mailto:${contact.email}`" class="text-muted">{{ contact.email }}</a>
                </div>
                <div v-if="contact.phone" class="col-md-4 text-center">
                    <i class="bi bi-telephone fs-2 text-primary mb-2"></i>
                    <h6>Phone</h6>
                    <p class="text-muted mb-0">{{ contact.phone }}</p>
                </div>
                <div v-if="contact.address" class="col-md-4 text-center">
                    <i class="bi bi-geo-alt fs-2 text-primary mb-2"></i>
                    <h6>Address</h6>
                    <p class="text-muted mb-0">{{ contact.address }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

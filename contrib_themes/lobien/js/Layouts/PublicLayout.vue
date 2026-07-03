<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import NavMenu from '../Components/NavMenu.vue';

const page = usePage();
const app = page.props.app ?? {};
const navigation = page.props.navigation ?? {};

const footerAbout = 'Lobien Realty Group is the No. 1 Realty Property Provider in the Philippines. We provide office and commercial spaces, lots and properties for sale/lease in the cities of Makati, Taguig, Pasig, Mandaluyong, San Juan, Pasay, Parañaque, Las Piñas, Quezon, and Caloocan.';
</script>

<template>
    <div class="d-flex flex-column min-vh-100">
        <header>
            <nav class="navbar navbar-expand-lg navbar-light bg-white lobien-navbar">
                <div class="lobien-container d-flex flex-wrap align-items-center w-100">
                    <Link href="/" class="navbar-brand site-brand text-decoration-none d-flex align-items-center py-2">
                        <img
                            v-if="app.logo"
                            :src="app.logo"
                            :alt="app.name"
                            class="site-logo"
                        >
                        <img
                            v-else
                            src="https://www.lobiengroup.com/sites/default/files/lobienrealty_logo-02_png.png"
                            :alt="app.name || 'Lobien Realty Group'"
                            class="site-logo"
                        >
                    </Link>

                    <button
                        class="navbar-toggler ms-auto"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#publicNavbar"
                        aria-controls="publicNavbar"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                    >
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse justify-content-lg-end" id="publicNavbar">
                        <NavMenu :items="navigation.header || []" />
                    </div>
                </div>
            </nav>
        </header>

        <main class="flex-grow-1">
            <slot />
        </main>

        <footer class="lobien-footer">
            <div class="lobien-container">
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-lg-4">
                        <h5>{{ app.name || 'Lobien Realty Group' }}</h5>
                        <p class="lobien-footer-about">{{ footerAbout }}</p>
                    </div>
                    <div v-if="(navigation.footer || []).length" class="col-md-3 col-lg-2">
                        <h6>Quick Links</h6>
                        <ul>
                            <li v-for="(item, i) in navigation.footer.slice(0, 8)" :key="i">
                                <Link v-if="!item.url.startsWith('http')" :href="item.url">{{ item.title }}</Link>
                                <a v-else :href="item.url" :target="item.target">{{ item.title }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <h6>Connect</h6>
                        <div class="d-flex gap-2 mb-3">
                            <a v-if="app.social?.facebook" :href="app.social.facebook" class="text-dark"><i class="bi bi-facebook"></i></a>
                            <a v-if="app.social?.twitter" :href="app.social.twitter" class="text-dark"><i class="bi bi-twitter-x"></i></a>
                            <a v-if="app.social?.instagram" :href="app.social.instagram" class="text-dark"><i class="bi bi-instagram"></i></a>
                            <a v-if="app.social?.linkedin" :href="app.social.linkedin" class="text-dark"><i class="bi bi-linkedin"></i></a>
                        </div>
                        <p v-if="app.contact?.phone" class="small mb-1">
                            <a :href="`tel:${app.contact.phone}`">{{ app.contact.phone }}</a>
                        </p>
                        <p v-if="app.contact?.email" class="small mb-0">
                            <a :href="`mailto:${app.contact.email}`">{{ app.contact.email }}</a>
                        </p>
                    </div>
                </div>
                <hr class="border-secondary my-4">
                <p class="lobien-footer-copyright text-center mb-0">
                    &copy; {{ new Date().getFullYear() }} {{ app.name || 'Lobien Realty Group, Inc.' }}. All rights reserved.
                    <span class="lobien-footer-tagline d-block mt-1">Buy &amp; Sell Property PH</span>
                </p>
            </div>
        </footer>
    </div>
</template>

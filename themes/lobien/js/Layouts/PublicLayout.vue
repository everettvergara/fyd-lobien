<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import NavMenu from '../Components/NavMenu.vue';

const page = usePage();
const app = page.props.app ?? {};
const navigation = page.props.navigation ?? {};

const footerAbout = 'Lobien Realty Group is the No. 1 Realty Property Provider in the Philippines. We provide office and commercial spaces, lots and properties for sale/lease in the cities of Makati, Taguig, Pasig, Mandaluyong, San Juan, Pasay, Parañaque, Las Piñas, Quezon, and Caloocan.';

const footerColumns = [
    {
        title: 'Join Us',
        links: [
            { title: 'Partner With Us', url: '#' },
            { title: 'Careers', url: '/careers' },
        ],
    },
    {
        title: 'LRG Bulletin',
        links: [
            { title: 'Talks', url: '#' },
            { title: 'Video Podcast', url: '#' },
            { title: 'Vlogs/Interviews', url: '#' },
            { title: 'Property Tours', url: '#' },
            { title: 'Downloadables', url: '#' },
        ],
    },
    {
        title: 'About Us',
        links: [
            { title: 'History', url: '#' },
            { title: 'Vision', url: '#' },
            { title: 'Mission', url: '#' },
            { title: 'Our People', url: '/about' },
        ],
    },
    {
        title: 'What We Offer',
        links: [
            { title: 'Project Leasing', url: '#' },
            { title: 'Tenant Solutions', url: '#' },
            { title: 'Property Sale and Acquisition', url: '#' },
            { title: 'For Lease Office and Retail', url: '#' },
            { title: 'For Sale Office and Retail', url: '#' },
        ],
    },
];

const officeAddress = '23F High Street South Corporate Plaza, Tower 1, 26th Street Corner 9th Avenue, Bonifacio Global City Taguig City, Philippines 1630';
const phonePrimary = app.contact?.phone || '+63 999 227 7125';
const phoneDirect = '+632 8983 9311';
const email = app.contact?.email || 'inquiry@lobiengroup.com';
</script>

<template>
    <div class="d-flex flex-column min-vh-100">
        <header class="lobien-header">
            <nav class="navbar navbar-expand-lg navbar-light bg-white lobien-navbar">
                <div class="lobien-container">
                    <div class="d-flex flex-wrap align-items-center w-100">
                        <Link href="/" class="navbar-brand lobien-navbar-brand text-decoration-none d-flex align-items-center">
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
                            class="navbar-toggler lobien-navbar-toggler ms-auto"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#publicNavbar"
                            aria-controls="publicNavbar"
                            aria-expanded="false"
                            aria-label="Toggle navigation"
                        >
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse flex-grow-0" id="publicNavbar">
                            <NavMenu :items="navigation.header || []" />
                        </div>
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
                    <div class="col-lg-4">
                        <h5>{{ app.name || 'Lobien Realty Group' }}</h5>
                        <p class="lobien-footer-about">{{ footerAbout }}</p>
                        <p class="lobien-footer-address small mb-2">{{ officeAddress }}</p>
                        <p class="small mb-1">
                            Call or Message us at <a :href="`tel:${phonePrimary.replace(/\s/g, '')}`">{{ phonePrimary }}</a>
                        </p>
                        <p class="small mb-1">
                            Direct Call at <a :href="`tel:${phoneDirect.replace(/\s/g, '')}`">{{ phoneDirect }}</a>
                        </p>
                        <p class="small mb-0">
                            <a :href="`mailto:${email}`">{{ email }}</a>
                        </p>
                    </div>

                    <div
                        v-for="(column, index) in footerColumns"
                        :key="index"
                        class="col-6 col-md-4 col-lg-2"
                    >
                        <h6>{{ column.title }}</h6>
                        <ul>
                            <li v-for="(link, linkIndex) in column.links" :key="linkIndex">
                                <Link v-if="!link.url.startsWith('http') && link.url !== '#'" :href="link.url">{{ link.title }}</Link>
                                <a v-else-if="link.url.startsWith('http')" :href="link.url" target="_blank" rel="noopener noreferrer">{{ link.title }}</a>
                                <a v-else href="#" @click.prevent>{{ link.title }}</a>
                            </li>
                        </ul>
                    </div>

                    <div v-if="(navigation.footer || []).length" class="col-6 col-md-4 col-lg-2">
                        <h6>Quick Links</h6>
                        <ul>
                            <li v-for="(item, i) in navigation.footer.slice(0, 8)" :key="i">
                                <Link v-if="!item.url.startsWith('http')" :href="item.url">{{ item.title }}</Link>
                                <a v-else :href="item.url" :target="item.target">{{ item.title }}</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <h6>Follow Us</h6>
                        <div class="d-flex gap-2 mb-3 lobien-footer-social">
                            <a v-if="app.social?.facebook" :href="app.social.facebook" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook"></i></a>
                            <a v-if="app.social?.instagram" :href="app.social.instagram" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram"></i></a>
                            <a v-if="app.social?.linkedin" :href="app.social.linkedin" target="_blank" rel="noopener noreferrer"><i class="bi bi-linkedin"></i></a>
                            <a v-if="app.social?.tiktok" :href="app.social.tiktok" target="_blank" rel="noopener noreferrer"><i class="bi bi-tiktok"></i></a>
                            <a v-if="app.social?.youtube" :href="app.social.youtube" target="_blank" rel="noopener noreferrer"><i class="bi bi-youtube"></i></a>
                        </div>
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

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import NavMenu from '../Components/NavMenu.vue';
import LobienBrandStrip from '../Components/LobienBrandStrip.vue';
import FooterLinkList from '../Components/FooterLinkList.vue';
import { useSocialLinks } from '../composables/useSocialLinks';

const DESKTOP_BREAKPOINT = '(min-width: 992px)';

let desktopMediaQuery;

function resetMobileNav() {
    const nav = document.getElementById('publicNavbar');
    const toggler = document.querySelector('.lobien-navbar-toggler');

    if (!nav) {
        return;
    }

    const collapse = window.bootstrap?.Collapse?.getInstance(nav)
        ?? window.bootstrap?.Collapse?.getOrCreateInstance(nav, { toggle: false });

    collapse?.hide();

    nav.classList.remove('collapsing');
    nav.removeAttribute('style');
    toggler?.setAttribute('aria-expanded', 'false');
}

function handleBreakpointChange(event) {
    if (event.matches) {
        resetMobileNav();
    }
}

onMounted(() => {
    desktopMediaQuery = window.matchMedia(DESKTOP_BREAKPOINT);
    desktopMediaQuery.addEventListener('change', handleBreakpointChange);

    if (desktopMediaQuery.matches) {
        resetMobileNav();
    }
});

onUnmounted(() => {
    desktopMediaQuery?.removeEventListener('change', handleBreakpointChange);
});

const page = usePage();
const app = page.props.app ?? {};
const navigation = page.props.navigation ?? {};
const { socialLinks } = useSocialLinks();

const joinUsLinks = [
    { title: 'Partner With Us', url: '#' },
    { title: 'Careers', url: '/careers' },
];

const lrgBulletinLinks = [
    { title: 'Talks', url: '#' },
    { title: 'Video Podcast', url: '#' },
    { title: 'Vlogs/Interviews', url: '#' },
    { title: 'Property Tours', url: '#' },
    { title: 'Downloadables', url: '#' },
];

const aboutUsLinks = [
    { title: 'About Us', url: '/about' },
];

const whatWeOfferLinks = [
    { title: 'Project Leasing', url: '/services' },
    { title: 'Tenant Solutions', url: '/services' },
    { title: 'Property Sale and Acquisition', url: '/services' },
    { title: 'For Lease Office and Retail', url: '#' },
    { title: 'For Sale Office and Retail', url: '#' },
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
                <div class="lobien-container lobien-navbar-container">
                    <div class="lobien-navbar-inner d-flex flex-wrap align-items-center w-100">
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

                        <div class="collapse navbar-collapse lobien-navbar-collapse flex-grow-0" id="publicNavbar">
                            <NavMenu :items="navigation.header || []" />
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <main class="flex-grow-1">
            <slot />
        </main>

        <LobienBrandStrip />

        <footer class="lobien-footer">
            <div class="lobien-container">
                <div class="row g-4 mb-4 lobien-footer-main">
                    <div class="col-lg-9">
                        <div class="row g-4 lobien-footer-nav">
                            <div class="col-6 col-md-4 col-lg-3 lobien-footer-stack">
                                <FooterLinkList title="Join Us" :links="joinUsLinks" />
                                <FooterLinkList title="LRG Bulletin" :links="lrgBulletinLinks" />
                                <div>
                                    <h6 class="lobien-footer-group-title">Follow Us</h6>
                                    <div class="d-flex gap-2 lobien-footer-social">
                                        <a
                                            v-for="item in socialLinks"
                                            :key="item.key"
                                            :href="item.url"
                                            :aria-label="item.label"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <i class="bi" :class="item.icon" />
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-lg-2">
                                <FooterLinkList title="About Us" :links="aboutUsLinks" />
                            </div>

                            <div class="col-12 col-md-6 col-lg-4 lobien-footer-offer-col">
                                <FooterLinkList title="What We Offer" :links="whatWeOfferLinks" />
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 lobien-footer-contact order-lg-last">
                        <h5>{{ app.name || 'Lobien Realty Group, Inc.' }}</h5>
                        <p class="lobien-footer-address mb-2">{{ officeAddress }}</p>
                        <p class="mb-1">
                            Call or Message us at <a :href="`tel:${phonePrimary.replace(/\s/g, '')}`">{{ phonePrimary }}</a>
                        </p>
                        <p class="mb-1">
                            Direct Call at <a :href="`tel:${phoneDirect.replace(/\s/g, '')}`">{{ phoneDirect }}</a>
                        </p>
                        <p class="mb-0">
                            <a :href="`mailto:${email}`">{{ email }}</a>
                        </p>
                    </div>
                </div>

                <div class="lobien-footer-legal text-center">
                    <Link href="/privacy-policy">Privacy Policy</Link>
                    <span class="lobien-footer-legal-sep" aria-hidden="true">|</span>
                    <Link href="/terms-of-use">Terms of Use</Link>
                </div>

                <p class="lobien-footer-copyright text-center mb-0">
                    &copy; {{ new Date().getFullYear() }} {{ app.name || 'Lobien Realty Group, Inc.' }}. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>

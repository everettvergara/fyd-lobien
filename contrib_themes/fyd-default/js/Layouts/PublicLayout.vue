<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import NavMenu from '../Components/NavMenu.vue';

const page = usePage();
const app = page.props.app ?? {};
const navigation = page.props.navigation ?? {};
</script>

<template>
    <div class="d-flex flex-column min-vh-100">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
            <div class="container">
                <Link href="/" class="navbar-brand site-brand text-decoration-none d-flex align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <img v-if="app.logo" :src="app.logo" :alt="app.name" class="site-logo" style="max-height: 40px">
                        <div class="site-brand-text">
                            <div class="site-brand-title">{{ app.name || 'FYD CMS' }}</div>
                            <div v-if="app.tagline" class="site-brand-tagline">{{ app.tagline }}</div>
                        </div>
                    </div>
                </Link>

                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#publicNavbar"
                    aria-controls="publicNavbar"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="publicNavbar">
                    <NavMenu :items="navigation.header || []" />
                </div>
            </div>
        </nav>

        <main class="flex-grow-1">
            <slot />
        </main>

        <footer class="public-footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-white mb-3">{{ app.name || 'FYD CMS' }}</h5>
                        <p class="small">{{ app.tagline || 'Professional corporate website platform.' }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-white mb-3">Quick Links</h6>
                        <ul class="list-unstyled small">
                            <li v-for="(item, i) in (navigation.footer || []).slice(0, 5)" :key="i" class="mb-2">
                                <Link v-if="!item.url.startsWith('http')" :href="item.url">{{ item.title }}</Link>
                                <a v-else :href="item.url" :target="item.target">{{ item.title }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-white mb-3">Connect</h6>
                        <div class="d-flex gap-2">
                            <a v-if="app.social?.facebook" :href="app.social.facebook" class="text-white"><i class="bi bi-facebook"></i></a>
                            <a v-if="app.social?.twitter" :href="app.social.twitter" class="text-white"><i class="bi bi-twitter-x"></i></a>
                            <a v-if="app.social?.instagram" :href="app.social.instagram" class="text-white"><i class="bi bi-instagram"></i></a>
                            <a v-if="app.social?.linkedin" :href="app.social.linkedin" class="text-white"><i class="bi bi-linkedin"></i></a>
                        </div>
                    </div>
                </div>
                <hr class="border-secondary my-4">
                <p class="small text-center mb-0">&copy; {{ new Date().getFullYear() }} {{ app.name || 'FYD CMS' }}. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>

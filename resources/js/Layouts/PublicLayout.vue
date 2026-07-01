<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    app: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <div class="d-flex flex-column min-vh-100">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
            <div class="container">
                <Link href="/" class="navbar-brand fw-bold text-primary">
                    {{ $page.props.app?.name || 'FYD CMS' }}
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
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#about">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#contact">Contact</a>
                        </li>
                    </ul>
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
                        <h5 class="text-white mb-3">{{ $page.props.app?.name || 'FYD CMS' }}</h5>
                        <p class="small">Professional corporate website platform powered by Laravel and Bootstrap.</p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-white mb-3">Quick Links</h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2"><a href="/">Home</a></li>
                            <li class="mb-2"><a href="#about">About</a></li>
                            <li class="mb-2"><a href="#contact">Contact</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-white mb-3">Legal</h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2"><a href="#">Privacy Policy</a></li>
                            <li class="mb-2"><a href="#">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
                <hr class="border-secondary my-4">
                <p class="small text-center mb-0">&copy; {{ new Date().getFullYear() }} {{ $page.props.app?.name || 'FYD CMS' }}. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>

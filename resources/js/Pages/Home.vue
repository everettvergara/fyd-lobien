<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import HeroBanner from '@/Components/HeroBanner.vue';

defineProps({
    hero: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <PublicLayout>
        <HeroBanner
            :title="hero.title"
            :subtitle="hero.subtitle"
            :description="hero.description"
            :button-text="hero.buttonText"
            :button-url="hero.buttonUrl"
        />

        <section id="about" class="public-section">
            <div class="container">
                <div class="row justify-content-center text-center mb-5">
                    <div class="col-lg-8">
                        <h2 class="fw-bold">Built for Growth</h2>
                        <p class="text-muted lead">
                            FYD CMS provides a solid foundation for corporate websites, marketing pages, and landing pages — with room to grow into e-commerce, CRM, and more.
                        </p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="feature-icon mx-auto mb-3">
                                <i class="bi bi-puzzle"></i>
                            </div>
                            <h5 class="fw-semibold">Modular</h5>
                            <p class="text-muted small">Every feature is a self-contained module following a consistent structure.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="feature-icon mx-auto mb-3">
                                <i class="bi bi-phone"></i>
                            </div>
                            <h5 class="fw-semibold">Mobile First</h5>
                            <p class="text-muted small">Responsive Bootstrap 5 design that looks great on every device.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="feature-icon mx-auto mb-3">
                                <i class="bi bi-search"></i>
                            </div>
                            <h5 class="fw-semibold">SEO Ready</h5>
                            <p class="text-muted small">Built-in SEO support for every content type from day one.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="public-section public-section-alt">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h3 class="fw-bold mb-2">Ready to get started?</h3>
                        <p class="text-muted mb-0">Contact us to learn how FYD CMS can power your next project.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="#contact" class="btn btn-primary btn-lg">Contact Us</a>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="public-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6 text-center">
                        <h2 class="fw-bold mb-3">Contact</h2>
                        <p class="text-muted">Contact form and details will be configured in Phase 4 (Settings module).</p>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

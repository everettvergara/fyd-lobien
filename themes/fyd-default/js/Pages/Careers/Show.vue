<script setup>
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SeoHead from '../../Components/SeoHead.vue';
import Breadcrumb from '../../Components/Breadcrumb.vue';
import CareerApplicationForm from '../../Components/CareerApplicationForm.vue';

defineProps({
    job: { type: Object, required: true },
    seo: { type: Object, default: () => ({}) },
});
</script>

<template>
    <PublicLayout>
        <SeoHead :seo="seo" />

        <div class="container py-5">
            <Breadcrumb :items="[
                { label: 'Careers', href: '/careers' },
                { label: job.title },
            ]" />

            <div class="row g-4">
                <div class="col-lg-7">
                    <article>
                        <header class="mb-4">
                            <h1 class="display-5 fw-bold">{{ job.title }}</h1>
                            <ul class="list-inline text-muted small mb-0">
                                <li v-if="job.department" class="list-inline-item"><strong>Department:</strong> {{ job.department }}</li>
                                <li v-if="job.location" class="list-inline-item"><strong>Location:</strong> {{ job.location }}</li>
                                <li class="list-inline-item"><strong>Type:</strong> {{ job.employment_type_label }}</li>
                                <li v-if="job.salary_range" class="list-inline-item"><strong>Salary:</strong> {{ job.salary_range }}</li>
                                <li v-if="job.closing_date" class="list-inline-item"><strong>Closes:</strong> {{ job.closing_date }}</li>
                            </ul>
                        </header>

                        <img
                            v-if="job.picture"
                            :src="job.picture.url"
                            :alt="job.picture.alt"
                            class="img-fluid rounded mb-4"
                        >

                        <div v-if="job.summary" class="lead text-muted mb-4">{{ job.summary }}</div>

                        <section v-if="job.description" class="mb-4">
                            <h2 class="h4 fw-bold mb-3">Description</h2>
                            <div class="content-body" v-html="job.description" />
                        </section>

                        <section v-if="job.requirements">
                            <h2 class="h4 fw-bold mb-3">Requirements</h2>
                            <div class="content-body" v-html="job.requirements" />
                        </section>
                    </article>
                </div>

                <div class="col-lg-5">
                    <div class="position-sticky" style="top: 2rem;">
                        <CareerApplicationForm :job="job" />
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

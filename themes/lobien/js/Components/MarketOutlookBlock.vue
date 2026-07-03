<script setup>
const outlookImage = 'https://www.lobiengroup.com/sites/default/files/image%20display/263f5d8a6e641115e2a31136e2b7b191.jpg';
</script>

<template>
    <section class="lobien-outlook-block">
        <div class="lobien-container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <h2>Real Estate Market Outlook Reports</h2>
                    <p>
                        Be informed about the current performance and trends of the Philippine commercial real estate.
                        Fill out your details and download our latest market outlook now!
                    </p>
                    <form action="#" method="post" @submit.prevent class="mt-4">
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Name" aria-label="Name">
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Email" aria-label="Email">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Company" aria-label="Company">
                        </div>
                        <p class="small opacity-75">
                            CAPTCHA — This question is for testing whether or not you are a human visitor and to prevent automated spam submissions.
                        </p>
                        <div class="text-end">
                            <button type="submit" class="btn btn-outlook">Download</button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-6 lobien-outlook-image d-none d-lg-block">
                    <img :src="outlookImage" alt="Market outlook" loading="lazy">
                </div>
            </div>
        </div>
    </section>
</template>

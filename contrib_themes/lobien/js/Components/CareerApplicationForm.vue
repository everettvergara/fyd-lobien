<script setup>
import { useRecaptcha } from '../composables/useRecaptcha';
import { ref } from 'vue';

const props = defineProps({
    job: { type: Object, required: true },
});

const { execute } = useRecaptcha();

const submitting = ref(false);
const submitError = ref('');
const fieldErrors = ref({});
const successMessage = ref('');

const form = ref({
    name: '',
    email: '',
    contact_number: '',
    remarks: '',
});
const resumeFile = ref(null);

function fieldError(key) {
    return fieldErrors.value[key]?.[0] ?? '';
}

function onFileChange(event) {
    const file = event.target.files?.[0] ?? null;
    resumeFile.value = file;
}

async function submitApplication() {
    if (submitting.value) {
        return;
    }

    submitting.value = true;
    submitError.value = '';
    fieldErrors.value = {};
    successMessage.value = '';

    try {
        const recaptchaToken = await execute(`career_apply_${props.job.slug}`);
        const formData = new FormData();

        formData.append('name', form.value.name);
        formData.append('email', form.value.email);
        formData.append('contact_number', form.value.contact_number);
        formData.append('remarks', form.value.remarks);
        formData.append('recaptcha_token', recaptchaToken);

        if (resumeFile.value) {
            formData.append('resume', resumeFile.value);
        }

        const response = await fetch(`/api/careers/jobs/${encodeURIComponent(props.job.slug)}/apply`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            },
            body: formData,
        });

        const payload = await response.json().catch(() => ({}));

        if (response.status === 422) {
            fieldErrors.value = payload.errors ?? {};
            submitError.value = payload.message ?? 'Please correct the errors below.';
            return;
        }

        if (!response.ok) {
            throw new Error(payload.message ?? 'Submission failed.');
        }

        successMessage.value = payload.message ?? 'Thank you for your application.';
        form.value = { name: '', email: '', contact_number: '', remarks: '' };
        resumeFile.value = null;
    } catch (error) {
        submitError.value = error instanceof Error ? error.message : 'Submission failed.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h2 class="h4 fw-bold mb-3">Apply for this role</h2>

            <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>

            <form @submit.prevent="submitApplication">
                <div class="mb-3">
                    <label class="form-label" for="career-name">Name <span class="text-danger">*</span></label>
                    <input
                        id="career-name"
                        v-model="form.name"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': fieldError('name') }"
                        required
                    >
                    <div v-if="fieldError('name')" class="invalid-feedback">{{ fieldError('name') }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="career-email">Email <span class="text-danger">*</span></label>
                    <input
                        id="career-email"
                        v-model="form.email"
                        type="email"
                        class="form-control"
                        :class="{ 'is-invalid': fieldError('email') }"
                        required
                    >
                    <div v-if="fieldError('email')" class="invalid-feedback">{{ fieldError('email') }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="career-contact">Contact Number <span class="text-danger">*</span></label>
                    <input
                        id="career-contact"
                        v-model="form.contact_number"
                        type="tel"
                        class="form-control"
                        :class="{ 'is-invalid': fieldError('contact_number') }"
                        required
                    >
                    <div v-if="fieldError('contact_number')" class="invalid-feedback">{{ fieldError('contact_number') }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="career-remarks">Remarks</label>
                    <textarea
                        id="career-remarks"
                        v-model="form.remarks"
                        class="form-control"
                        :class="{ 'is-invalid': fieldError('remarks') }"
                        rows="3"
                    />
                    <div v-if="fieldError('remarks')" class="invalid-feedback">{{ fieldError('remarks') }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="career-resume">Resume (PDF only) <span class="text-danger">*</span></label>
                    <input
                        id="career-resume"
                        type="file"
                        accept="application/pdf,.pdf"
                        class="form-control"
                        :class="{ 'is-invalid': fieldError('resume') }"
                        required
                        @change="onFileChange"
                    >
                    <div class="form-text">Maximum file size: 10 MB.</div>
                    <div v-if="fieldError('resume')" class="invalid-feedback">{{ fieldError('resume') }}</div>
                </div>

                <div v-if="submitError" class="alert alert-danger">{{ submitError }}</div>
                <div v-if="fieldErrors.recaptcha_token" class="alert alert-danger">
                    {{ fieldErrors.recaptcha_token[0] }}
                </div>

                <button type="submit" class="btn btn-primary w-100" :disabled="submitting">
                    {{ submitting ? 'Submitting...' : 'Submit Application' }}
                </button>
            </form>
        </div>
    </div>
</template>

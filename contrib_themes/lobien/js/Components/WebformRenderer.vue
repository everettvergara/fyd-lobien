<script setup>
import { useRecaptcha } from '../composables/useRecaptcha';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    slug: { type: String, required: true },
    wrapperClass: { type: String, default: '' },
});

const { enabled: recaptchaEnabled, execute } = useRecaptcha();

const loading = ref(true);
const submitting = ref(false);
const loadError = ref('');
const submitError = ref('');
const fieldErrors = ref({});
const successMessage = ref('');
const form = ref(null);

const fieldValues = ref({});

const fields = computed(() => form.value?.fields ?? []);
const settings = computed(() => form.value?.settings ?? {});

async function loadForm() {
    loading.value = true;
    loadError.value = '';

    try {
        const response = await fetch(`/api/webforms/${encodeURIComponent(props.slug)}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Form not found.');
        }

        form.value = await response.json();
        const initial = {};

        for (const field of form.value.fields ?? []) {
            initial[field.key] = field.type === 'checkbox' ? false : '';
        }

        fieldValues.value = initial;
    } catch (error) {
        loadError.value = error instanceof Error ? error.message : 'Unable to load form.';
    } finally {
        loading.value = false;
    }
}

function inputType(type) {
    if (type === 'datetime') {
        return 'datetime-local';
    }

    if (['text', 'email', 'tel', 'number', 'date', 'hidden'].includes(type)) {
        return type;
    }

    return 'text';
}

function fieldError(key) {
    return fieldErrors.value[`fields.${key}`]?.[0]
        ?? fieldErrors.value[key]?.[0]
        ?? '';
}

async function submitForm() {
    if (submitting.value || !form.value) {
        return;
    }

    submitting.value = true;
    submitError.value = '';
    fieldErrors.value = {};
    successMessage.value = '';

    try {
        const recaptchaToken = await execute(`webform_${props.slug}`);

        const response = await fetch(`/api/webforms/${encodeURIComponent(props.slug)}/submit`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            },
            body: JSON.stringify({
                fields: fieldValues.value,
                recaptcha_token: recaptchaToken,
            }),
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

        successMessage.value = payload.message ?? settings.value.success_message ?? 'Thank you for your submission.';

        if (payload.redirect_url) {
            window.location.href = payload.redirect_url;
            return;
        }

        for (const field of fields.value) {
            fieldValues.value[field.key] = field.type === 'checkbox' ? false : '';
        }
    } catch (error) {
        submitError.value = error instanceof Error ? error.message : 'Submission failed.';
    } finally {
        submitting.value = false;
    }
}

onMounted(loadForm);
</script>

<template>
    <div :class="wrapperClass">
        <div v-if="loading" class="text-muted">Loading form...</div>
        <div v-else-if="loadError" class="alert alert-danger">{{ loadError }}</div>

        <template v-else-if="form">
            <div v-if="form.description" class="mb-3 text-muted">{{ form.description }}</div>

            <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>

            <form @submit.prevent="submitForm">
                <div v-for="field in fields" :key="field.key" class="mb-3">
                    <template v-if="field.type === 'hidden'">
                        <input v-model="fieldValues[field.key]" type="hidden">
                    </template>

                    <template v-else-if="field.type === 'checkbox'">
                        <div class="form-check">
                            <input
                                :id="`webform-${slug}-${field.key}`"
                                v-model="fieldValues[field.key]"
                                class="form-check-input"
                                :class="{ 'is-invalid': fieldError(field.key) }"
                                type="checkbox"
                            >
                            <label class="form-check-label" :for="`webform-${slug}-${field.key}`">
                                {{ field.label }}
                                <span v-if="field.required" class="text-danger">*</span>
                            </label>
                        </div>
                    </template>

                    <template v-else-if="field.type === 'radio'">
                        <label class="form-label d-block">
                            {{ field.label }}
                            <span v-if="field.required" class="text-danger">*</span>
                        </label>
                        <div v-for="option in field.options" :key="option.value" class="form-check">
                            <input
                                :id="`webform-${slug}-${field.key}-${option.value}`"
                                v-model="fieldValues[field.key]"
                                class="form-check-input"
                                :class="{ 'is-invalid': fieldError(field.key) }"
                                type="radio"
                                :value="option.value"
                            >
                            <label class="form-check-label" :for="`webform-${slug}-${field.key}-${option.value}`">
                                {{ option.label }}
                            </label>
                        </div>
                    </template>

                    <template v-else-if="field.type === 'select'">
                        <label class="form-label" :for="`webform-${slug}-${field.key}`">
                            {{ field.label }}
                            <span v-if="field.required" class="text-danger">*</span>
                        </label>
                        <select
                            :id="`webform-${slug}-${field.key}`"
                            v-model="fieldValues[field.key]"
                            class="form-select"
                            :class="{ 'is-invalid': fieldError(field.key) }"
                        >
                            <option value="">Select...</option>
                            <option v-for="option in field.options" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </template>

                    <template v-else-if="field.type === 'textarea'">
                        <label class="form-label" :for="`webform-${slug}-${field.key}`">
                            {{ field.label }}
                            <span v-if="field.required" class="text-danger">*</span>
                        </label>
                        <textarea
                            :id="`webform-${slug}-${field.key}`"
                            v-model="fieldValues[field.key]"
                            class="form-control"
                            :class="{ 'is-invalid': fieldError(field.key) }"
                            rows="4"
                            :placeholder="field.placeholder"
                        />
                    </template>

                    <template v-else>
                        <label class="form-label" :for="`webform-${slug}-${field.key}`">
                            {{ field.label }}
                            <span v-if="field.required" class="text-danger">*</span>
                        </label>
                        <input
                            :id="`webform-${slug}-${field.key}`"
                            v-model="fieldValues[field.key]"
                            class="form-control"
                            :class="{ 'is-invalid': fieldError(field.key) }"
                            :type="inputType(field.type)"
                            :placeholder="field.placeholder"
                        >
                    </template>

                    <div v-if="field.help" class="form-text">{{ field.help }}</div>
                    <div v-if="fieldError(field.key)" class="invalid-feedback d-block">
                        {{ fieldError(field.key) }}
                    </div>
                </div>

                <div v-if="submitError" class="alert alert-danger">{{ submitError }}</div>
                <div v-if="fieldErrors.recaptcha_token" class="alert alert-danger">
                    {{ fieldErrors.recaptcha_token[0] }}
                </div>

                <button type="submit" class="btn btn-search" :disabled="submitting">
                    {{ submitting ? 'Submitting...' : (settings.submit_label || 'Submit') }}
                </button>
            </form>
        </template>
    </div>
</template>

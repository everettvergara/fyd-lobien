<script setup>
import { useRecaptcha } from '../composables/useRecaptcha';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    slug: { type: String, default: '' },
    name: { type: String, default: '' },
    description: { type: String, default: '' },
});

const { execute } = useRecaptcha();

const fieldLabels = {
    name: 'Name',
    mobile_number: 'Mobile number',
    designation: 'Designation',
    company: 'Company',
};

const loading = ref(true);
const submitting = ref(false);
const loadError = ref('');
const submitError = ref('');
const fieldErrors = ref({});
const successMessage = ref('');
const newsletter = ref(null);
const guestEmail = ref('');
const profile = ref({
    name: '',
    mobile_number: '',
    designation: '',
    company: '',
});

const profileFieldOrder = ['name', 'mobile_number', 'designation', 'company'];

const settings = computed(() => newsletter.value?.settings ?? {});
const auth = computed(() => newsletter.value?.auth ?? { logged_in: false, email: null, name: null, subscribed: false });
const isSubscribed = computed(() => auth.value.subscribed === true);
const isLoggedIn = computed(() => auth.value.logged_in === true);
const enabledProfileFields = computed(() => {
    const fields = settings.value.fields ?? {};

    return profileFieldOrder
        .filter((key) => Boolean(fields[key]?.enabled))
        .map((key) => ({
            key,
            required: Boolean(fields[key]?.required),
            label: fieldLabels[key] ?? key,
        }));
});
const showProfileFields = computed(() => !isSubscribed.value && enabledProfileFields.value.length > 0);

function resetProfile(authData = {}) {
    profile.value = {
        name: authData.name ?? '',
        mobile_number: '',
        designation: '',
        company: '',
    };
}

function fieldLabel(field) {
    return field.required ? field.label : `${field.label} (optional)`;
}

async function loadNewsletter() {
    if (!props.slug) {
        loading.value = false;
        return;
    }

    loading.value = true;
    loadError.value = '';

    try {
        const response = await fetch(`/api/newsletters/${encodeURIComponent(props.slug)}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Newsletter not found.');
        }

        newsletter.value = await response.json();

        if (newsletter.value.auth?.email) {
            guestEmail.value = newsletter.value.auth.email;
        }

        resetProfile(newsletter.value.auth ?? {});
    } catch (error) {
        loadError.value = error instanceof Error ? error.message : 'Unable to load newsletter.';
    } finally {
        loading.value = false;
    }
}

async function postAction(action) {
    if (submitting.value || !newsletter.value) {
        return;
    }

    submitting.value = true;
    submitError.value = '';
    fieldErrors.value = {};
    successMessage.value = '';

    try {
        const recaptchaToken = await execute(`newsletter_${props.slug}_${action}`);

        const body = { recaptcha_token: recaptchaToken };

        if (!isLoggedIn.value) {
            body.email = guestEmail.value;
        }

        if (action === 'subscribe') {
            enabledProfileFields.value.forEach(({ key }) => {
                body[key] = profile.value[key] ?? '';
            });
        }

        const response = await fetch(`/api/newsletters/${encodeURIComponent(props.slug)}/${action}`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            },
            body: JSON.stringify(body),
        });

        const payload = await response.json().catch(() => ({}));

        if (response.status === 422) {
            fieldErrors.value = payload.errors ?? {};
            submitError.value = payload.message ?? 'Please correct the errors below.';
            return;
        }

        if (!response.ok) {
            throw new Error(payload.message ?? 'Request failed.');
        }

        successMessage.value = payload.message ?? '';
        newsletter.value = {
            ...newsletter.value,
            auth: payload.auth ?? newsletter.value.auth,
        };
    } catch (error) {
        submitError.value = error instanceof Error ? error.message : 'Request failed.';
    } finally {
        submitting.value = false;
    }
}

function subscribe() {
    return postAction('subscribe');
}

function unsubscribe() {
    return postAction('unsubscribe');
}

function fieldError(key) {
    return fieldErrors.value[key]?.[0] ?? '';
}

onMounted(loadNewsletter);
</script>

<template>
    <div v-if="!slug" />

    <section v-else class="newsletter-block">
        <h2 v-if="name" class="newsletter-block__name h4 fw-semibold mb-2">{{ name }}</h2>
        <p v-if="description" class="newsletter-block__description text-muted mb-3">{{ description }}</p>

        <div v-if="loading" class="text-muted">Loading...</div>
        <div v-else-if="loadError" class="alert alert-danger">{{ loadError }}</div>

        <template v-else-if="newsletter">
            <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>

        <form @submit.prevent="isSubscribed ? unsubscribe() : subscribe()">
            <template v-if="!isLoggedIn && !isSubscribed">
                <div class="mb-3">
                    <label class="form-label" :for="`newsletter-${slug}-email`">Email</label>
                    <input
                        :id="`newsletter-${slug}-email`"
                        v-model="guestEmail"
                        type="email"
                        class="form-control"
                        :class="{ 'is-invalid': fieldError('email') }"
                        :placeholder="settings.placeholder_email || 'you@example.com'"
                        required
                    >
                    <div v-if="fieldError('email')" class="invalid-feedback d-block">
                        {{ fieldError('email') }}
                    </div>
                </div>
            </template>

            <template v-else-if="isLoggedIn">
                <p class="mb-3">
                    <span v-if="isSubscribed">Subscribed as </span>
                    <span v-else>Subscribe as </span>
                    <strong>{{ auth.email }}</strong>
                </p>
            </template>

            <template v-if="showProfileFields">
                <div v-for="field in enabledProfileFields" :key="field.key" class="mb-3">
                    <label class="form-label" :for="`newsletter-${slug}-${field.key}`">
                        {{ fieldLabel(field) }}
                    </label>
                    <input
                        :id="`newsletter-${slug}-${field.key}`"
                        :value="profile[field.key]"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': fieldError(field.key) }"
                        :required="field.required"
                        @input="profile[field.key] = $event.target.value"
                    >
                    <div v-if="fieldError(field.key)" class="invalid-feedback d-block">
                        {{ fieldError(field.key) }}
                    </div>
                </div>
            </template>

            <div v-if="submitError" class="alert alert-danger">{{ submitError }}</div>
            <div v-if="fieldErrors.recaptcha_token" class="alert alert-danger">
                {{ fieldErrors.recaptcha_token[0] }}
            </div>

            <button
                type="submit"
                class="btn"
                :class="isSubscribed ? 'btn-outline-secondary' : 'btn-primary'"
                :disabled="submitting"
            >
                {{
                    submitting
                        ? 'Please wait...'
                        : (isSubscribed
                            ? (settings.unsubscribe_label || 'Unsubscribe')
                            : (settings.subscribe_label || 'Subscribe'))
                }}
            </button>
        </form>
        </template>
    </section>
</template>

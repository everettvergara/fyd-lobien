<script setup>
import { useRecaptcha } from '../composables/useRecaptcha';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    slug: { type: String, default: '' },
});

const { execute } = useRecaptcha();

const loading = ref(true);
const submitting = ref(false);
const loadError = ref('');
const submitError = ref('');
const fieldErrors = ref({});
const successMessage = ref('');
const newsletter = ref(null);
const guestEmail = ref('');
const guestName = ref('');

const settings = computed(() => newsletter.value?.settings ?? {});
const auth = computed(() => newsletter.value?.auth ?? { logged_in: false, email: null, subscribed: false });
const isSubscribed = computed(() => auth.value.subscribed === true);
const isLoggedIn = computed(() => auth.value.logged_in === true);

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
            if (guestName.value) {
                body.name = guestName.value;
            }
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
    <div v-else-if="loading" class="text-muted">Loading...</div>
    <div v-else-if="loadError" class="alert alert-danger">{{ loadError }}</div>

    <template v-else-if="newsletter">
        <div v-if="newsletter.description" class="mb-3 text-muted">{{ newsletter.description }}</div>
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

                <div class="mb-3">
                    <label class="form-label" :for="`newsletter-${slug}-name`">Name (optional)</label>
                    <input
                        :id="`newsletter-${slug}-name`"
                        v-model="guestName"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': fieldError('name') }"
                    >
                    <div v-if="fieldError('name')" class="invalid-feedback d-block">
                        {{ fieldError('name') }}
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
</template>

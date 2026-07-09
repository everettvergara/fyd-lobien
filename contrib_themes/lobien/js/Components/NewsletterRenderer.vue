<script setup>
import { toRef } from 'vue';
import { useNewsletter } from '../composables/useNewsletter';

const props = defineProps({
    slug: { type: String, required: true },
    wrapperClass: { type: String, default: '' },
});

const {
    loading,
    submitting,
    loadError,
    submitError,
    fieldErrors,
    successMessage,
    newsletter,
    guestEmail,
    profile,
    settings,
    auth,
    isSubscribed,
    isLoggedIn,
    enabledProfileFields,
    showProfileFields,
    fieldLabel,
    fieldError,
    subscribe,
    unsubscribe,
} = useNewsletter(toRef(props, 'slug'));
</script>

<template>
    <div :class="wrapperClass">
        <div v-if="loading" class="text-muted">Loading...</div>
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
                            v-model="profile[field.key]"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': fieldError(field.key) }"
                            :required="field.required"
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
    </div>
</template>

<script setup>
import { computed, toRef } from 'vue';
import outlookImage from '../../assets/images/market-outlook.jpg';
import { useNewsletter } from '../composables/useNewsletter';

const props = defineProps({
    slug: { type: String, required: true },
    name: { type: String, default: '' },
    description: { type: String, default: '' },
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
    fieldError,
    subscribe,
    unsubscribe,
} = useNewsletter(toRef(props, 'slug'));

const profilePlaceholders = {
    name: 'Name',
    mobile_number: 'Mobile number',
    designation: 'Designation',
    company: 'Company',
};

const displayName = computed(() => props.name || newsletter.value?.name || '');
const displayDescription = computed(() => props.description || newsletter.value?.description || '');

const profileFieldsBeforeEmail = computed(() => enabledProfileFields.value.filter((field) => field.key === 'name'));
const profileFieldsAfterEmail = computed(() => enabledProfileFields.value.filter((field) => field.key !== 'name'));
const visibleProfileFields = computed(() => (
    isLoggedIn.value ? enabledProfileFields.value : profileFieldsAfterEmail.value
));
</script>

<template>
    <section v-if="slug" class="lobien-outlook-block">
        <div class="lobien-container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <h2 v-if="displayName">{{ displayName }}</h2>
                    <p v-if="displayDescription">{{ displayDescription }}</p>

                    <div v-if="loading" class="text-white-50">Loading...</div>
                    <div v-else-if="loadError" class="alert alert-danger lobien-outlook-alert">{{ loadError }}</div>

                    <template v-else-if="newsletter">
                    <div v-if="successMessage" class="alert alert-success lobien-outlook-alert">{{ successMessage }}</div>

                    <form class="mt-4 lobien-outlook-form" @submit.prevent="isSubscribed ? unsubscribe() : subscribe()">
                        <template v-if="!isLoggedIn && !isSubscribed">
                            <div v-for="field in profileFieldsBeforeEmail" :key="field.key" class="mb-3">
                                <input
                                    :id="`newsletter-outlook-${slug}-${field.key}`"
                                    v-model="profile[field.key]"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': fieldError(field.key) }"
                                    :placeholder="profilePlaceholders[field.key] ?? field.label"
                                    :aria-label="field.label"
                                    :required="field.required"
                                >
                                <div v-if="fieldError(field.key)" class="invalid-feedback d-block">
                                    {{ fieldError(field.key) }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <input
                                    :id="`newsletter-outlook-${slug}-email`"
                                    v-model="guestEmail"
                                    type="email"
                                    class="form-control"
                                    :class="{ 'is-invalid': fieldError('email') }"
                                    :placeholder="settings.placeholder_email || 'Email'"
                                    aria-label="Email"
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

                        <template v-if="showProfileFields && visibleProfileFields.length">
                            <div v-for="field in visibleProfileFields" :key="field.key" class="mb-3">
                                <input
                                    :id="`newsletter-outlook-${slug}-${field.key}`"
                                    v-model="profile[field.key]"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': fieldError(field.key) }"
                                    :placeholder="profilePlaceholders[field.key] ?? field.label"
                                    :aria-label="field.label"
                                    :required="field.required"
                                >
                                <div v-if="fieldError(field.key)" class="invalid-feedback d-block">
                                    {{ fieldError(field.key) }}
                                </div>
                            </div>
                        </template>

                        <div v-if="submitError" class="alert alert-danger lobien-outlook-alert">{{ submitError }}</div>
                        <div v-if="fieldErrors.recaptcha_token" class="alert alert-danger lobien-outlook-alert">
                            {{ fieldErrors.recaptcha_token[0] }}
                        </div>

                        <div class="text-end">
                            <button
                                type="submit"
                                class="btn btn-outlook"
                                :disabled="submitting"
                            >
                                {{
                                    submitting
                                        ? 'Please wait...'
                                        : (isSubscribed
                                            ? (settings.unsubscribe_label || 'Unsubscribe')
                                            : (settings.subscribe_label || 'Download'))
                                }}
                            </button>
                        </div>
                    </form>
                    </template>
                </div>

                <div class="col-lg-6 lobien-outlook-image d-none d-lg-block">
                    <img :src="outlookImage" alt="" loading="lazy">
                </div>
            </div>
        </div>
    </section>
</template>

import { useRecaptcha } from './useRecaptcha';
import { computed, onMounted, ref, toValue, watch } from 'vue';

const fieldLabels = {
    name: 'Name',
    mobile_number: 'Mobile number',
    designation: 'Designation',
    company: 'Company',
};

const profileFieldOrder = ['name', 'mobile_number', 'designation', 'company'];

function resetProfile(profile, authData = {}) {
    profile.value = {
        name: authData.name ?? '',
        mobile_number: '',
        designation: '',
        company: '',
    };
}

export function useNewsletter(slugSource) {
    const { execute } = useRecaptcha();

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

    function fieldLabel(field) {
        return field.required ? field.label : `${field.label} (optional)`;
    }

    function fieldError(key) {
        return fieldErrors.value[key]?.[0] ?? '';
    }

    async function loadNewsletter() {
        const slug = toValue(slugSource);

        if (!slug) {
            loading.value = false;
            newsletter.value = null;
            return;
        }

        loading.value = true;
        loadError.value = '';

        try {
            const response = await fetch(`/api/newsletters/${encodeURIComponent(slug)}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error('Newsletter not found.');
            }

            newsletter.value = await response.json();

            if (newsletter.value.auth?.email) {
                guestEmail.value = newsletter.value.auth.email;
            }

            resetProfile(profile, newsletter.value.auth ?? {});
        } catch (error) {
            loadError.value = error instanceof Error ? error.message : 'Unable to load newsletter.';
        } finally {
            loading.value = false;
        }
    }

    async function postAction(action) {
        const slug = toValue(slugSource);

        if (submitting.value || !newsletter.value || !slug) {
            return;
        }

        submitting.value = true;
        submitError.value = '';
        fieldErrors.value = {};
        successMessage.value = '';

        try {
            const recaptchaToken = await execute(`newsletter_${slug}_${action}`);

            const body = { recaptcha_token: recaptchaToken };

            if (!isLoggedIn.value) {
                body.email = guestEmail.value;
            }

            if (action === 'subscribe') {
                enabledProfileFields.value.forEach(({ key }) => {
                    body[key] = profile.value[key] ?? '';
                });
            }

            const response = await fetch(`/api/newsletters/${encodeURIComponent(slug)}/${action}`, {
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

    watch(() => toValue(slugSource), loadNewsletter);

    onMounted(loadNewsletter);

    return {
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
        loadNewsletter,
        subscribe,
        unsubscribe,
    };
}

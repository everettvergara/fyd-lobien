let scriptPromise = null;

function loadScript(siteKey) {
    if (typeof window === 'undefined' || !siteKey) {
        return Promise.resolve();
    }

    if (window.grecaptcha) {
        return Promise.resolve();
    }

    if (scriptPromise) {
        return scriptPromise;
    }

    scriptPromise = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(siteKey)}`;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Failed to load reCAPTCHA.'));
        document.head.appendChild(script);
    });

    return scriptPromise;
}

async function executeRecaptcha(siteKey, action) {
    await loadScript(siteKey);

    return new Promise((resolve, reject) => {
        window.grecaptcha.ready(async () => {
            try {
                const token = await window.grecaptcha.execute(siteKey, { action });
                resolve(token);
            } catch (error) {
                reject(error);
            }
        });
    });
}

function setRecaptchaToken(form, token) {
    let input = form.querySelector('input[name="recaptcha_token"]');

    if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'recaptcha_token';
        form.appendChild(input);
    }

    input.value = token;
}

function initRecaptchaForms() {
    document.querySelectorAll('[data-recaptcha-form]').forEach((form) => {
        const siteKey = form.dataset.recaptchaSiteKey;
        const action = form.dataset.recaptchaAction;

        if (!siteKey || !action) {
            return;
        }

        form.addEventListener('submit', async (event) => {
            if (form.dataset.recaptchaSubmitting === '1') {
                return;
            }

            event.preventDefault();

            const submitButton = form.querySelector('[type="submit"]');

            if (submitButton) {
                submitButton.disabled = true;
            }

            try {
                const token = await executeRecaptcha(siteKey, action);
                setRecaptchaToken(form, token);
                form.dataset.recaptchaSubmitting = '1';
                form.submit();
            } catch {
                form.dataset.recaptchaSubmitting = '0';

                if (submitButton) {
                    submitButton.disabled = false;
                }
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', initRecaptchaForms);

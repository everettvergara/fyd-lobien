import { usePage } from '@inertiajs/vue3';

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

export function useRecaptcha() {
    const page = usePage();
    const recaptcha = page.props.recaptcha ?? { enabled: false, siteKey: null };

    async function execute(action) {
        if (!recaptcha.enabled || !recaptcha.siteKey) {
            return '';
        }

        await loadScript(recaptcha.siteKey);

        return new Promise((resolve, reject) => {
            window.grecaptcha.ready(async () => {
                try {
                    const token = await window.grecaptcha.execute(recaptcha.siteKey, { action });
                    resolve(token);
                } catch (error) {
                    reject(error);
                }
            });
        });
    }

    return {
        enabled: recaptcha.enabled,
        execute,
    };
}

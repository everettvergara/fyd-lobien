import './bootstrap';
import 'bootstrap';
import '../scss/public.scss';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

function getPrimaryColor() {
    if (typeof document === 'undefined') {
        return '#2563eb';
    }

    return getComputedStyle(document.documentElement).getPropertyValue('--fyd-color-primary').trim()
        || '#2563eb';
}

createInertiaApp({
    title: (title) => (title ? `${title} — FYD CMS` : 'FYD CMS'),
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: getPrimaryColor(),
    },
});

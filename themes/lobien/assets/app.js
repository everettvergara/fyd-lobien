import '../js/bootstrap.js';
import 'bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolveThemePage } from '../../_shared/resolveInertiaPage.js';
import { registerThemeBlocks } from '../../_shared/resolveBlockComponent.js';

const themePages = import.meta.glob('../js/Pages/**/*.vue');
const fallbackPages = import.meta.glob('../../fyd-default/js/Pages/**/*.vue');
const themeBlocks = import.meta.glob('../js/blocks/*.vue');

registerThemeBlocks(themeBlocks);

function getPrimaryColor() {
    if (typeof document === 'undefined') {
        return '#2563eb';
    }

    return getComputedStyle(document.documentElement).getPropertyValue('--fyd-color-primary').trim()
        || '#2563eb';
}

createInertiaApp({
    title: (title) => (title ? `${title} — FYD CMS` : 'FYD CMS'),
    resolve: (name) => resolveThemePage(name, themePages, fallbackPages),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: getPrimaryColor(),
    },
});

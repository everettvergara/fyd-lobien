import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const FALLBACK_THEME_SLUG = 'fyd-default';

/**
 * Resolve an Inertia page from the active theme, falling back to fyd-default
 * when optional module pages (Careers/Show, Webforms/Show, etc.) are missing.
 *
 * Page globs must be created in each theme's assets/app.js — import.meta.glob
 * paths are resolved relative to the file that declares them, not this helper.
 */
export function resolveThemePage(name, themePages, fallbackPages) {
    const themePath = `../js/Pages/${name}.vue`;
    const fallbackPath = `../../${FALLBACK_THEME_SLUG}/js/Pages/${name}.vue`;
    const pages = { ...fallbackPages, ...themePages };

    return resolvePageComponent([themePath, fallbackPath], pages);
}

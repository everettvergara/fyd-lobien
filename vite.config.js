import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';
import { existsSync, readdirSync, readFileSync } from 'fs';

function discoverThemeInputs() {
    const themesPath = resolve(__dirname, 'themes');

    if (!existsSync(themesPath)) {
        return [];
    }

    const inputs = [];

    for (const slug of readdirSync(themesPath)) {
        const themeDir = resolve(themesPath, slug);
        const manifestPath = resolve(themeDir, 'theme.json');

        if (!existsSync(manifestPath)) {
            continue;
        }

        try {
            const manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));
            const scss = manifest.assets?.scss ?? 'scss/theme.scss';
            const js = manifest.assets?.js ?? 'assets/app.js';

            inputs.push(`themes/${slug}/${scss}`);
            inputs.push(`themes/${slug}/${js}`);
        } catch {
            continue;
        }
    }

    return inputs;
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                ...discoverThemeInputs(),
                'resources/admin/scss/app.scss',
                'resources/admin/js/app.js',
            ],
            refresh: [
                'resources/views/**',
                'routes/**',
                'app/Modules/**/Views/**',
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        watch: {
            ignored: [
                '**/storage/**',
                '**/vendor/**',
                '**/bootstrap/cache/**',
                '**/contrib/**',
                '**/app/Modules/**/Database/**',
                '**/app/Modules/**/Migrations/**',
                '**/app/Modules/**/Tests/**',
            ],
            ...(process.platform === 'win32' ? { usePolling: true, interval: 1000 } : {}),
        },
    },
});

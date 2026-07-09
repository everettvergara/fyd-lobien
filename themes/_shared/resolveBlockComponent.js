import { defineAsyncComponent, computed } from 'vue';

const sharedBlocks = import.meta.glob('./blocks/*.vue');

let themeBlockLoaders = {};

export function registerThemeBlocks(loaders) {
    themeBlockLoaders = loaders ?? {};
}

function blockTypeToComponentName(type) {
    return type
        .split('-')
        .filter(Boolean)
        .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
        .join('')
        + 'Block';
}

function findBlockLoader(name, loaders) {
    const suffix = `/blocks/${name}.vue`;
    const direct = loaders[`./blocks/${name}.vue`];

    if (direct) {
        return direct;
    }

    return Object.entries(loaders).find(([key]) => key.replace(/\\/g, '/').endsWith(suffix))?.[1] ?? null;
}

function resolveLoader(name) {
    const candidates = [name];

    if (!name.endsWith('Block')) {
        candidates.push(blockTypeToComponentName(name));
    }

    for (const candidate of candidates) {
        const themeLoader = findBlockLoader(candidate, themeBlockLoaders);

        if (themeLoader) {
            return themeLoader;
        }

        const sharedLoader = findBlockLoader(candidate, sharedBlocks);

        if (sharedLoader) {
            return sharedLoader;
        }
    }

    return findBlockLoader('FallbackBlock', sharedBlocks);
}

export function resolveBlockComponent(name) {
    if (!name) {
        return null;
    }

    const loader = resolveLoader(name);

    return loader ? defineAsyncComponent(loader) : null;
}

export function useBlockComponent(name) {
    return computed(() => resolveBlockComponent(name));
}

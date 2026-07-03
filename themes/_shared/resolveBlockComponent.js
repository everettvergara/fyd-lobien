import { defineAsyncComponent, computed } from 'vue';

const sharedBlocks = import.meta.glob('./blocks/*.vue');

function blockTypeToComponentName(type) {
    return type
        .split('-')
        .filter(Boolean)
        .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
        .join('')
        + 'Block';
}

function findBlockLoader(name) {
    const suffix = `/blocks/${name}.vue`;
    const direct = sharedBlocks[`./blocks/${name}.vue`];

    if (direct) {
        return direct;
    }

    return Object.entries(sharedBlocks).find(([key]) => key.replace(/\\/g, '/').endsWith(suffix))?.[1] ?? null;
}

export function resolveBlockComponent(name) {
    if (!name) {
        return null;
    }

    const candidates = [name];

    if (!name.endsWith('Block')) {
        candidates.push(blockTypeToComponentName(name));
    }

    for (const candidate of candidates) {
        const loader = findBlockLoader(candidate);

        if (loader) {
            return defineAsyncComponent(loader);
        }
    }

    const fallback = findBlockLoader('FallbackBlock');

    return fallback ? defineAsyncComponent(fallback) : null;
}

export function useBlockComponent(name) {
    return computed(() => resolveBlockComponent(name));
}

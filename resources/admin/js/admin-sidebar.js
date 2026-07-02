const SIDEBAR_SECTIONS_STORAGE_KEY = 'admin-sidebar-sections';
const SIDEBAR_PANEL_STORAGE_KEY = 'admin-sidebar-panel-hidden';

function readSidebarSectionState() {
    try {
        return JSON.parse(localStorage.getItem(SIDEBAR_SECTIONS_STORAGE_KEY) || '{}');
    } catch {
        return {};
    }
}

function writeSidebarSectionState(state) {
    localStorage.setItem(SIDEBAR_SECTIONS_STORAGE_KEY, JSON.stringify(state));
}

export function initAdminSidebarSections() {
    const shouldReset = document.body.dataset.adminSidebarReset === '1';
    let saved = shouldReset ? {} : readSidebarSectionState();

    if (shouldReset) {
        writeSidebarSectionState(saved);
    }

    document.querySelectorAll('[data-admin-nav-section-key]').forEach((toggle) => {
        const key = toggle.dataset.adminNavSectionKey;
        const targetSelector = toggle.getAttribute('data-bs-target');

        if (! key || ! targetSelector) {
            return;
        }

        const target = document.querySelector(targetSelector);

        if (! target) {
            return;
        }

        const hasActive = Boolean(target.querySelector('.nav-link.active'));
        const collapse = window.bootstrap.Collapse.getOrCreateInstance(target, { toggle: false });

        if (hasActive || saved[key] === true) {
            collapse.show();
        } else {
            collapse.hide();
        }

        target.addEventListener('shown.bs.collapse', () => {
            saved[key] = true;
            writeSidebarSectionState(saved);
            toggle.setAttribute('aria-expanded', 'true');
        });

        target.addEventListener('hidden.bs.collapse', () => {
            saved[key] = false;
            writeSidebarSectionState(saved);
            toggle.setAttribute('aria-expanded', 'false');
        });
    });
}

function isSidebarPanelHidden() {
    try {
        return localStorage.getItem(SIDEBAR_PANEL_STORAGE_KEY) === '1';
    } catch {
        return false;
    }
}

function setSidebarPanelHidden(hidden) {
    try {
        localStorage.setItem(SIDEBAR_PANEL_STORAGE_KEY, hidden ? '1' : '0');
    } catch {
        // ignore storage failures
    }
}

function applySidebarPanelState(hidden) {
    document.body.classList.toggle('admin-sidebar-hidden', hidden);
    document.documentElement.classList.toggle('admin-sidebar-hidden', hidden);

    const toggle = document.querySelector('[data-admin-sidebar-toggle]');

    if (! toggle) {
        return;
    }

    const iconVisible = toggle.querySelector('[data-sidebar-icon-visible]');
    const iconHidden = toggle.querySelector('[data-sidebar-icon-hidden]');

    toggle.setAttribute('aria-pressed', hidden ? 'true' : 'false');
    toggle.setAttribute('aria-label', hidden ? 'Show menu' : 'Hide menu');
    toggle.setAttribute('title', hidden ? 'Show menu' : 'Hide menu');

    iconVisible?.classList.toggle('d-none', hidden);
    iconHidden?.classList.toggle('d-none', ! hidden);
}

export function initAdminSidebarPanelToggle() {
    applySidebarPanelState(isSidebarPanelHidden());

    const toggle = document.querySelector('[data-admin-sidebar-toggle]');

    if (! toggle) {
        return;
    }

    toggle.addEventListener('click', () => {
        const hidden = ! document.body.classList.contains('admin-sidebar-hidden');
        setSidebarPanelHidden(hidden);
        applySidebarPanelState(hidden);
    });
}

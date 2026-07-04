import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

import './media-picker.js';
import './media-library.js';
import './admin-list.js';
import './listing-comparator.js';
import './rich-text-editor.js';
import './password-toggle.js';
import { initAdminSidebarPanelToggle, initAdminSidebarSections } from './admin-sidebar.js';

window.showToast = function (message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toastId = `toast-${Date.now()}`;
    const bgClass = type === 'success' ? 'text-bg-success' : type === 'error' ? 'text-bg-danger' : 'text-bg-primary';

    const toastHtml = `
        <div id="${toastId}" class="toast ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', toastHtml);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
};

window.confirmAction = function (message, callback) {
    if (confirm(message)) {
        callback();
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('adminSidebar');

    if (sidebar) {
        sidebar.classList.add('offcanvas-lg', 'offcanvas-start');
        sidebar.setAttribute('tabindex', '-1');

        document.querySelectorAll('[data-bs-target="#adminSidebar"]').forEach((button) => {
            button.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        });

        initAdminSidebarSections();
        initAdminSidebarPanelToggle();
    }
});

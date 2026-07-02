document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-admin-list-search]').forEach((input) => {
        let timeout = null;
        input.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                input.closest('form')?.requestSubmit();
            }, 400);
        });
    });

    document.querySelectorAll('[data-admin-list-bulk-form]').forEach((root) => {
        const submit = root.querySelector('[data-admin-list-bulk-submit]');
        const count = root.querySelector('[data-admin-list-selected-count]');
        const action = root.querySelector('[data-admin-list-bulk-action]');
        const bulkInputs = Array.from(root.querySelectorAll('[data-admin-list-bulk-input]'));
        const formId = action?.getAttribute('form') || submit?.getAttribute('form');
        const form = formId ? document.getElementById(formId) : null;
        const scope = root.closest('.card') || document;
        const selectAll = scope.querySelector('[data-admin-list-select-all]');
        const checkboxes = Array.from(scope.querySelectorAll('[data-admin-list-row-checkbox]'));

        const syncBulkInputs = () => {
            const selectedAction = action?.value ?? '';

            bulkInputs.forEach((input) => {
                const matches = input.dataset.bulkAction === selectedAction;
                input.hidden = !matches;
                input.disabled = !matches;
                if (!matches) {
                    input.value = '';
                }
            });
        };

        const update = () => {
            const selected = checkboxes.filter((checkbox) => checkbox.checked).length;
            const selectedAction = action?.value ?? '';
            const activeInput = bulkInputs.find((input) => input.dataset.bulkAction === selectedAction);
            const inputReady = !activeInput || activeInput.value !== '';

            if (count) count.textContent = `${selected} selected`;
            if (submit) submit.disabled = selected === 0 || !selectedAction || !inputReady;
            if (selectAll) {
                selectAll.checked = selected > 0 && selected === checkboxes.length;
                selectAll.indeterminate = selected > 0 && selected < checkboxes.length;
            }

            syncBulkInputs();
        };

        selectAll?.addEventListener('change', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
            update();
        });

        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', update));
        action?.addEventListener('change', update);
        bulkInputs.forEach((input) => input.addEventListener('change', update));

        form?.addEventListener('submit', (event) => {
            const selectedAction = action?.selectedOptions?.[0];
            const message = selectedAction?.dataset?.confirm;
            if (message && !confirm(message)) {
                event.preventDefault();
            }
        });

        update();
    });
});

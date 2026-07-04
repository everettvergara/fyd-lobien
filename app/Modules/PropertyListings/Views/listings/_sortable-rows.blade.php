@once
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const SORT_STEP = 10;

        const updateSortOrders = (tbody) => {
            const rows = tbody.querySelectorAll(tbody.dataset.sortableRow || 'tr');
            rows.forEach((row, index) => {
                const sortInput = row.querySelector('[data-sort-order]');
                if (sortInput) {
                    sortInput.value = index * SORT_STEP;
                }
            });
        };

        window.listingSortableRefresh = (tbody) => {
            if (tbody) {
                updateSortOrders(tbody);
            }
        };

        document.querySelectorAll('[data-sortable-tbody]').forEach((tbody) => {
            const rowSelector = tbody.dataset.sortableRow || 'tr';
            let draggedRow = null;

            tbody.addEventListener('dragstart', (event) => {
                const row = event.target.closest(rowSelector);
                if (!row || !row.hasAttribute('draggable')) {
                    return;
                }

                draggedRow = row;
                row.classList.add('table-active');
                event.dataTransfer.effectAllowed = 'move';
            });

            tbody.addEventListener('dragend', () => {
                if (draggedRow) {
                    draggedRow.classList.remove('table-active');
                    draggedRow = null;
                }
                updateSortOrders(tbody);
            });

            tbody.addEventListener('dragover', (event) => {
                event.preventDefault();
                if (!draggedRow) {
                    return;
                }

                const targetRow = event.target.closest(rowSelector);
                if (!targetRow || targetRow === draggedRow) {
                    return;
                }

                const rect = targetRow.getBoundingClientRect();
                const after = event.clientY > rect.top + rect.height / 2;
                tbody.insertBefore(draggedRow, after ? targetRow.nextSibling : targetRow);
            });

            updateSortOrders(tbody);
        });
    });
    </script>
    @endpush
@endonce

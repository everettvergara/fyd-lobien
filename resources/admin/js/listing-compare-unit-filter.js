const parseNumber = (value) => {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
};

const readFilterState = (root) => {
    const get = (name) => {
        const input = root.querySelector(`[name="${name}"]`);
        return input ? input.value.trim() : '';
    };

    return {
        areaMin: parseNumber(get('area_min')),
        areaMax: parseNumber(get('area_max')),
        rentMin: parseNumber(get('rent_min')),
        rentMax: parseNumber(get('rent_max')),
        priceMin: parseNumber(get('price_min')),
        priceMax: parseNumber(get('price_max')),
        ho: get('ho'),
        avl: get('avl'),
        bds: get('bds'),
        type: get('type'),
        lease: get('lease'),
        sale: get('sale'),
    };
};

const countActiveFilters = (state) => {
    let count = 0;

    if (state.areaMin !== null) count += 1;
    if (state.areaMax !== null) count += 1;
    if (state.rentMin !== null) count += 1;
    if (state.rentMax !== null) count += 1;
    if (state.priceMin !== null) count += 1;
    if (state.priceMax !== null) count += 1;
    if (state.ho) count += 1;
    if (state.avl) count += 1;
    if (state.bds) count += 1;
    if (state.type) count += 1;
    if (state.lease !== '') count += 1;
    if (state.sale !== '') count += 1;

    return count;
};

const inRange = (value, min, max) => {
    const numeric = parseNumber(value);

    if (numeric === null) {
        return min === null && max === null;
    }

    if (min !== null && numeric < min) {
        return false;
    }

    if (max !== null && numeric > max) {
        return false;
    }

    return true;
};

const rowMatches = (row, state) => {
    if (!inRange(row.dataset.unitArea, state.areaMin, state.areaMax)) {
        return false;
    }

    if (!inRange(row.dataset.unitRental, state.rentMin, state.rentMax)) {
        return false;
    }

    if (!inRange(row.dataset.unitPrice, state.priceMin, state.priceMax)) {
        return false;
    }

    if (state.ho && row.dataset.unitHo !== state.ho) {
        return false;
    }

    if (state.avl && row.dataset.unitAvl !== state.avl) {
        return false;
    }

    if (state.bds && row.dataset.unitBds !== state.bds) {
        return false;
    }

    if (state.type && row.dataset.unitType !== state.type) {
        return false;
    }

    if (state.lease !== '' && row.dataset.unitLease !== state.lease) {
        return false;
    }

    if (state.sale !== '' && row.dataset.unitSale !== state.sale) {
        return false;
    }

    return true;
};

const updateFilterBadge = (root, state) => {
    const badge = root.querySelector('[data-listing-compare-unit-filter-count]');
    if (!badge) {
        return;
    }

    const count = countActiveFilters(state);
    badge.textContent = String(count);
    badge.classList.toggle('d-none', count === 0);
};

const applyUnitFilter = (root) => {
    const state = readFilterState(root);
    updateFilterBadge(root, state);

    document.querySelectorAll('.listing-compare-units-table').forEach((table) => {
        const rows = table.querySelectorAll('[data-listing-compare-unit-row]');
        const noMatchRow = table.querySelector('[data-listing-compare-unit-no-match]');
        let visibleCount = 0;

        rows.forEach((row) => {
            const matches = rowMatches(row, state);
            row.classList.toggle('d-none', !matches);

            if (matches) {
                visibleCount += 1;
            }
        });

        if (noMatchRow) {
            noMatchRow.classList.toggle('d-none', visibleCount > 0);
        }
    });
};

const resetUnitFilter = (root) => {
    root.querySelectorAll('[data-listing-compare-unit-filter-input]').forEach((input) => {
        input.value = '';
    });

    applyUnitFilter(root);
};

const initListingCompareUnitFilter = () => {
    const root = document.querySelector('[data-listing-compare-unit-filter]');
    if (!root) {
        return;
    }

    const panel = root.querySelector('[data-listing-compare-unit-filter-panel]');
    const toggle = root.querySelector('[data-listing-compare-unit-filter-toggle]');
    const applyButton = root.querySelector('[data-listing-compare-unit-filter-apply]');
    const resetButton = root.querySelector('[data-listing-compare-unit-filter-reset]');

    if (toggle && panel) {
        toggle.addEventListener('click', () => {
            const open = !panel.classList.contains('show');
            panel.classList.toggle('show', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    applyButton?.addEventListener('click', () => applyUnitFilter(root));
    resetButton?.addEventListener('click', () => resetUnitFilter(root));

    applyUnitFilter(root);
};

document.addEventListener('DOMContentLoaded', initListingCompareUnitFilter);

export { initListingCompareUnitFilter, applyUnitFilter, resetUnitFilter };

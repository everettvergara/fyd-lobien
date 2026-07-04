const STORAGE_KEY = 'fyd-listing-comparator';
const MAX_ITEMS = 5;

const readBin = () => {
    try {
        const parsed = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]');
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
};

const writeBin = (items) => {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(items.slice(0, MAX_ITEMS)));
};

const listingFromButton = (button) => ({
    id: String(button.dataset.listingId || ''),
    code: button.dataset.listingCode || '',
    name: button.dataset.listingName || button.dataset.listingCode || 'Listing',
});

const syncCompareButtons = () => {
    const ids = new Set(readBin().map((item) => item.id));

    document.querySelectorAll('[data-listing-compare]').forEach((button) => {
        const inBin = ids.has(String(button.dataset.listingId || ''));
        button.classList.toggle('active', inBin);
        button.classList.toggle('btn-primary', inBin);
        button.classList.toggle('btn-outline-secondary', !inBin);
        button.title = inBin ? 'Remove from compare bin' : 'Add to compare bin';
    });
};

const renderBin = () => {
    const bin = document.querySelector('[data-listing-comparator-bin]');
    if (!bin) {
        return;
    }

    const items = readBin();
    const chips = bin.querySelector('[data-listing-comparator-chips]');
    const countBadge = bin.querySelector('[data-listing-comparator-count]');

    bin.classList.toggle('d-none', items.length === 0);

    if (countBadge) {
        countBadge.textContent = String(items.length);
    }

    if (chips) {
        chips.innerHTML = items.map((item) => `
            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1">
                <span>${item.code || item.name}</span>
                <button type="button"
                        class="btn-close btn-close-sm"
                        style="font-size:.55rem;"
                        data-listing-comparator-remove="${item.id}"
                        aria-label="Remove ${item.code || item.name} from compare bin"></button>
            </span>
        `).join('');
    }

    syncCompareButtons();
};

const toggleListing = (listing) => {
    if (!listing.id) {
        return;
    }

    let items = readBin();
    const index = items.findIndex((item) => item.id === listing.id);

    if (index >= 0) {
        items.splice(index, 1);
    } else {
        if (items.length >= MAX_ITEMS) {
            window.showToast?.(`Compare bin is full (max ${MAX_ITEMS}). Remove one listing first.`, 'error');
            return;
        }

        items.push(listing);
    }

    writeBin(items);
    renderBin();
};

const removeListing = (id) => {
    writeBin(readBin().filter((item) => item.id !== String(id)));
    renderBin();
};

const clearBin = () => {
    writeBin([]);
    renderBin();
};

const openCompare = () => {
    const bin = document.querySelector('[data-listing-comparator-bin]');
    const compareUrl = bin?.dataset.compareUrl || '/admin/listings/compare';
    const items = readBin();

    if (items.length < 2) {
        window.showToast?.('Add at least two listings to compare.', 'error');
        return;
    }

    const ids = items.map((item) => item.id).join(',');
    const url = new URL(compareUrl, window.location.origin);
    url.searchParams.set('ids', ids);
    window.location.href = url.toString();
};

const initListingComparator = () => {
    if (!document.querySelector('[data-listing-comparator-bin]') && !document.querySelector('[data-listing-compare]')) {
        return;
    }

    renderBin();

    document.addEventListener('click', (event) => {
        const compareButton = event.target.closest('[data-listing-compare]');
        if (compareButton) {
            event.preventDefault();
            toggleListing(listingFromButton(compareButton));
            return;
        }

        const removeButton = event.target.closest('[data-listing-comparator-remove]');
        if (removeButton) {
            event.preventDefault();
            removeListing(removeButton.dataset.listingComparatorRemove);
            return;
        }

        if (event.target.closest('[data-listing-comparator-clear]')) {
            event.preventDefault();
            clearBin();
            return;
        }

        if (event.target.closest('[data-listing-comparator-open]')) {
            event.preventDefault();
            openCompare();
        }
    });
};

document.addEventListener('DOMContentLoaded', initListingComparator);

export { STORAGE_KEY, MAX_ITEMS, readBin, writeBin, initListingComparator };

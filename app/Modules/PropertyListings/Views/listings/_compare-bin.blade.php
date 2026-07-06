<div class="listing-comparator-bin border-top d-none px-3 py-2"
     data-listing-comparator-bin
     data-compare-url="{{ route('admin.listings.compare') }}">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button"
                class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                data-listing-comparator-open
                title="Compare selected listings">
            <i class="{{ admin_icon('bi-intersect') }}" aria-hidden="true"></i>
            <span>Compare</span>
            <span class="badge bg-primary rounded-pill" data-listing-comparator-count>0</span>
        </button>
        <div class="d-flex flex-wrap gap-1 flex-grow-1" data-listing-comparator-chips></div>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-listing-comparator-clear>
            Clear all
        </button>
    </div>
</div>

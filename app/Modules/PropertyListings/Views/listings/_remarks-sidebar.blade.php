@php
    $listingModel = $listing ?? null;
    $remarks = $listingModel?->remarks?->loadMissing(['user.avatar', 'unit']) ?? collect();
    $unitsForFilter = $listingModel?->units ?? collect();
    $canAddRemarks = $listingModel?->exists ?? false;
@endphp

<div class="listing-remarks-shell position-relative" data-listing-remarks-panel>
    <div id="listing-remarks-panel-body"
         class="listing-remarks-panel card border-0 shadow-sm sticky-top"
         style="top:1rem;"
         data-listing-remarks-body>
        <div class="card-header py-2 small listing-remarks-header">
            <span class="fw-bold">Remarks</span>
        </div>
        <div class="card-body p-3">
            @if ($canAddRemarks)
                <form id="listing-remarks-form"
                      method="POST"
                      action="{{ route('admin.listings.remarks.store', $listingModel) }}"
                      class="mb-3 border-bottom pb-3 listing-remarks-form">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small" for="remark_unit_id">Unit (optional)</label>
                        <select class="form-select form-select-sm" id="remark_unit_id" name="listing_unit_id">
                            <option value="">Listing-level remark</option>
                            @foreach ($unitsForFilter as $unit)
                                <option value="{{ $unit->id }}" @selected(old('listing_unit_id') == $unit->id)>
                                    {{ trim(($unit->floor ? 'Fl. '.$unit->floor.' — ' : '').($unit->unit ?: 'Unit #'.$unit->id)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small" for="remark_comment">Comment</label>
                        <textarea class="form-control form-control-sm @error('comment') is-invalid @enderror"
                                  id="remark_comment"
                                  name="comment"
                                  rows="3"
                                  required>{{ old('comment') }}</textarea>
                        @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-sm w-100 listing-remarks-submit-btn">Add Remark</button>
                </form>

                <div class="mb-2">
                    <label class="form-label small" for="remarks_unit_filter">Filter feed</label>
                    <select class="form-select form-select-sm" id="remarks_unit_filter" data-remarks-unit-filter>
                        <option value="">All remarks</option>
                        @foreach ($unitsForFilter as $unit)
                            <option value="{{ $unit->id }}">
                                {{ trim(($unit->floor ? 'Fl. '.$unit->floor.' — ' : '').($unit->unit ?: 'Unit #'.$unit->id)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <p class="small mb-3 listing-remarks-muted">Save the listing once to add remarks.</p>
            @endif

            <div class="listing-remarks-feed" data-remarks-feed style="max-height:24rem;overflow-y:auto;">
                @forelse ($remarks as $remark)
                    @php
                        $remarkUser = $remark->user;
                        $avatarUrl = $remarkUser?->avatarUrl();
                        $initial = strtoupper((string) \Illuminate\Support\Str::of($remarkUser?->name ?? 'U')->substr(0, 1));
                    @endphp
                    <div class="listing-remark-card card border-0 shadow-sm mb-2"
                         data-remark-item
                         data-unit-id="{{ $remark->listing_unit_id ?? '' }}">
                        <div class="card-body p-2 small">
                            <div class="d-flex justify-content-between gap-2 mb-1">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    @if ($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="{{ $remarkUser?->name ?? 'User' }}" class="listing-remark-avatar">
                                    @else
                                        <span class="listing-remark-avatar listing-remark-avatar-fallback">{{ $initial }}</span>
                                    @endif
                                    <strong class="text-truncate">{{ $remarkUser?->name ?? 'User' }}</strong>
                                </div>
                                <span class="listing-remarks-muted text-nowrap">{{ $remark->remarked_at?->format('M j, Y g:i A') }}</span>
                            </div>
                            @if ($remark->unit)
                                <div class="listing-remarks-muted mb-1">
                                    Unit: {{ trim(($remark->unit->floor ? 'Fl. '.$remark->unit->floor.' — ' : '').($remark->unit->unit ?: '#'.$remark->unit->id)) }}
                                </div>
                            @endif
                            <div>{{ $remark->comment }}</div>
                        </div>
                    </div>
                @empty
                    <p class="small mb-0 listing-remarks-muted" data-remarks-empty>No remarks yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .listing-remarks-panel.card,
    .listing-remarks-panel .card-body {
        background-color: #1e293b !important;
        color: rgba(255, 255, 255, 0.88);
    }

    .listing-remarks-panel .card-header.listing-remarks-header {
        background-color: #1e293b !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        color: #fff;
    }

    .listing-remarks-submit-btn {
        background-color: #ffc000 !important;
        border: 1px solid #d9a300 !important;
        color: #0f172a !important;
        font-weight: 600;
    }

    .listing-remarks-submit-btn:hover,
    .listing-remarks-submit-btn:focus {
        background-color: #e6ac00 !important;
        border-color: #c49200 !important;
        color: #020617 !important;
    }

    .listing-remarks-muted {
        color: #94a3b8 !important;
    }

    .listing-remark-card.card {
        background: linear-gradient(135deg, #fef9c3 0%, #ffffff 100%) !important;
        border-left: 3px solid #eab308;
        border-radius: 0.375rem;
    }

    .listing-remark-card .card-body {
        background: transparent !important;
        color: #000;
    }

    .listing-remark-card .listing-remarks-muted {
        color: #475569 !important;
    }

    .listing-remark-avatar {
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 999px;
        object-fit: cover;
        flex: 0 0 auto;
    }

    .listing-remark-avatar-fallback {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #0d6efd;
        color: #fff;
        font-weight: 700;
    }

    .listing-remarks-panel .form-label,
    .listing-remarks-panel label.form-label.small {
        color: #fff !important;
    }

    .listing-remarks-panel .form-control,
    .listing-remarks-panel .form-select {
        background-color: #334155;
        border-color: rgba(255, 255, 255, 0.12);
        color: rgba(255, 255, 255, 0.92);
    }

    .listing-remarks-panel .form-control:focus,
    .listing-remarks-panel .form-select:focus {
        background-color: #334155;
        border-color: rgba(13, 110, 253, 0.65);
        color: rgba(255, 255, 255, 0.92);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    .listing-remarks-panel #remark_comment {
        background-color: #fff !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }

    .listing-remarks-panel #remark_comment:focus {
        background-color: #fff !important;
        border-color: #0d6efd !important;
        color: #0f172a !important;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    .listing-remarks-panel .listing-remarks-form {
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const STORAGE_KEY = 'listings_remarks_open';
    const editorRoot = document.querySelector('.listing-editor-sections');
    const toolbarToggle = document.querySelector('[data-listing-remarks-toggle]');
    const toggleLabels = document.querySelectorAll('[data-listing-remarks-toggle-label]');
    const filter = document.querySelector('[data-remarks-unit-filter]');
    const items = document.querySelectorAll('[data-remark-item]');

    const setRemarksOpen = (open) => {
        editorRoot?.classList.toggle('is-remarks-hidden', !open);
        toolbarToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');

        const hideTitle = 'Hide remarks';
        const showTitle = 'Show remarks';

        toolbarToggle?.setAttribute('title', open ? hideTitle : showTitle);

        toggleLabels.forEach((label) => {
            label.textContent = open ? hideTitle : showTitle;
        });

        sessionStorage.setItem(STORAGE_KEY, open ? '1' : '0');
    };

    const stored = sessionStorage.getItem(STORAGE_KEY);
    setRemarksOpen(stored === null ? true : stored === '1');

    toolbarToggle?.addEventListener('click', () => {
        const isOpen = !editorRoot?.classList.contains('is-remarks-hidden');
        setRemarksOpen(!isOpen);
    });

    filter?.addEventListener('change', () => {
        const unitId = filter.value;
        items.forEach((item) => {
            if (!unitId) {
                item.hidden = false;
                return;
            }
            item.hidden = item.dataset.unitId !== unitId;
        });
    });
});
</script>
@endpush

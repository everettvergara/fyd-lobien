@extends('admin.layouts.app')

@section('title', 'Property Listings Configuration')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Listings', 'url' => route('admin.listings.index')],
        ['label' => 'Configuration'],
    ]" />

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Property Listings Configuration</h1>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header py-2 small">Public Website</div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Generate the public property website: the <code>/properties</code> hub page (city cards),
                the <code>/properties/search</code> results page, city hub pages at
                <code>/properties/{city-slug}</code>, listing pages at
                <code>/properties/{city-slug}/{listing-slug}</code>, and a
                <strong>Properties</strong> footer menu. Only listings with
                <strong>Publish to PUBLIC</strong> enabled, a city, and a slug are included.
            </p>

            <dl class="row small mb-3">
                <dt class="col-sm-4">Eligible listings</dt>
                <dd class="col-sm-8"><strong>{{ $eligibleCount }}</strong></dd>
                <dt class="col-sm-4">Distinct cities</dt>
                <dd class="col-sm-8"><strong>{{ $cityCount }}</strong></dd>
                <dt class="col-sm-4">Existing /properties pages</dt>
                <dd class="col-sm-8"><strong>{{ $existingPropertyPages }}</strong></dd>
            </dl>

            <div id="property-page-gen-progress" class="d-none mb-3">
                <div class="progress" style="height: 1.25rem;">
                    <div id="property-page-gen-progress-bar"
                         class="progress-bar progress-bar-striped progress-bar-animated"
                         role="progressbar"
                         style="width: 0%"
                         aria-valuenow="0"
                         aria-valuemin="0"
                         aria-valuemax="100">0%</div>
                </div>
                <p id="property-page-gen-status" class="small text-muted mt-2 mb-0">Starting…</p>
            </div>

            @can('manage', App\Modules\PropertyListings\Models\ListingConfiguration::class)
                <div class="d-flex gap-2 align-items-center">
                    <button type="button"
                            id="property-page-gen-start"
                            class="btn btn-primary"
                            data-url="{{ route('admin.listings.configuration.generate-pages') }}"
                            data-status-url="{{ route('admin.listings.configuration.generate-pages.status') }}">
                        Generate Public Website
                    </button>

                    <form method="POST"
                          action="{{ route('admin.listings.configuration.clear-pages') }}"
                          onsubmit="return confirm('Remove ALL generated public property pages, blocks, and the Properties footer menu?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">Clear Public Website</button>
                    </form>
                </div>
            @endcan

            <p class="small text-muted mt-3 mb-0">
                Requires a queue worker (<code>php artisan queue:work</code>) unless <code>QUEUE_CONNECTION=sync</code>.
            </p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header py-2 small">Sample Data</div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Dropdown values are seeded automatically when the module is installed.
                Property listings are <strong>not</strong> created on install — use the button below
                to add or refresh five demo listings (<code>DEMO-001</code> … <code>DEMO-005</code>)
                with full form data, units, fees, remarks, and image assets.
            </p>

            <p class="small text-muted mb-3">
                Current demo listings: <strong>{{ $demoCount }}</strong>
            </p>

            @if (! ($gdAvailable ?? true))
                <div class="alert alert-warning small py-2">
                    PHP GD is not enabled. Sample assets will use a plain fallback image instead of labeled demo graphics.
                    Enable the <code>gd</code> extension for richer demo thumbnails.
                </div>
            @endif

            @can('manage', App\Modules\PropertyListings\Models\ListingConfiguration::class)
                <form method="POST"
                      action="{{ route('admin.listings.configuration.seed-samples') }}"
                      onsubmit="return confirm('Create or refresh 5 demo property listings (DEMO-001 … DEMO-005)?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">Seed Sample Listings</button>
                </form>
            @endcan
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const startButton = document.getElementById('property-page-gen-start');
            if (!startButton) {
                return;
            }

            const progressWrap = document.getElementById('property-page-gen-progress');
            const progressBar = document.getElementById('property-page-gen-progress-bar');
            const statusText = document.getElementById('property-page-gen-status');
            let pollTimer = null;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

            function setProgress(processed, total) {
                const percent = total > 0 ? Math.round((processed / total) * 100) : 0;
                progressBar.style.width = `${percent}%`;
                progressBar.textContent = `${percent}%`;
                progressBar.setAttribute('aria-valuenow', String(percent));
            }

            async function pollStatus(statusUrl, batchId) {
                const response = await fetch(`${statusUrl}?batch_id=${encodeURIComponent(batchId)}`, {
                    headers: { 'Accept': 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('Unable to read generation status.');
                }

                return response.json();
            }

            function finishUi(message, failed = false) {
                clearInterval(pollTimer);
                pollTimer = null;
                startButton.disabled = false;
                progressBar.classList.remove('progress-bar-animated');
                if (failed) {
                    progressBar.classList.add('bg-danger');
                } else {
                    progressBar.classList.add('bg-success');
                }
                statusText.textContent = message;
            }

            startButton.addEventListener('click', async () => {
                if (!confirm('Generate the public website (hub, search, city, and listing pages plus footer menu)?')) {
                    return;
                }

                startButton.disabled = true;
                progressWrap.classList.remove('d-none');
                progressBar.classList.remove('bg-danger', 'bg-success');
                progressBar.classList.add('progress-bar-animated');
                setProgress(0, 1);
                statusText.textContent = 'Starting…';

                try {
                    const response = await fetch(startButton.dataset.url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Unable to start page generation.');
                    }

                    const payload = await response.json();
                    const batchId = payload.batch_id;

                    pollTimer = setInterval(async () => {
                        try {
                            const status = await pollStatus(startButton.dataset.statusUrl, batchId);
                            const total = Number(status.total ?? 0);
                            const processed = Number(status.processed ?? 0);
                            setProgress(processed, total || 1);
                            statusText.textContent = status.message ?? 'Generating…';

                            if (status.status === 'completed') {
                                finishUi(status.message ?? 'Generation completed.');
                            }

                            if (status.status === 'failed') {
                                finishUi(status.message ?? 'Generation failed.', true);
                            }
                        } catch (error) {
                            finishUi(error instanceof Error ? error.message : 'Generation failed.', true);
                        }
                    }, 1000);
                } catch (error) {
                    finishUi(error instanceof Error ? error.message : 'Unable to start generation.', true);
                }
            });
        })();
    </script>
@endpush

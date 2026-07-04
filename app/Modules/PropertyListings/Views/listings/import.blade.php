@extends('admin.layouts.app')

@section('title', 'Import Listings')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Listings', 'url' => route('admin.listings.index')],
        ['label' => 'Import CSV'],
    ]" />

    <x-admin.page-header
        title="Import Listings"
        :back-route="route('admin.listings.index')"
        back-label="Back to Listings"
    />

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-2 small">Upload CSV</div>
                <div class="card-body">
                    <p class="small text-muted">
                        Upload a CSV with listing header columns and unit columns. Multiple rows with the same
                        <code>code</code> create or update multiple units. Review the preview before confirming.
                    </p>

                    <form method="POST" action="{{ route('admin.listings.import.preview') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="import_file" class="form-label">CSV File</label>
                            <input type="file"
                                   class="form-control @error('file') is-invalid @enderror"
                                   id="import_file"
                                   name="file"
                                   accept=".csv,text/csv"
                                   required>
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Preview Import</button>
                    </form>
                </div>
            </div>
        </div>

        @if (! empty($preview))
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header py-2 small d-flex justify-content-between align-items-center">
                        <span>Preview</span>
                        <span class="badge bg-secondary-subtle text-secondary">{{ count($preview['rows'] ?? []) }} rows</span>
                    </div>
                    <div class="card-body p-0">
                        @if (! empty($preview['errors']))
                            <div class="alert alert-warning m-3 small">
                                <ul class="mb-0 ps-3">
                                    @foreach ($preview['errors'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        @foreach ($preview['headers'] ?? [] as $header)
                                            <th class="small">{{ $header }}</th>
                                        @endforeach
                                        <th class="small">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($preview['rows'] ?? [] as $row)
                                        <tr>
                                            @foreach ($preview['headers'] ?? [] as $header)
                                                <td class="small">{{ $row[$header] ?? '' }}</td>
                                            @endforeach
                                            <td class="small">
                                                @if (($row['_action'] ?? '') === 'update')
                                                    <span class="badge bg-info-subtle text-info">Update</span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success">Create</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if (! empty($importKey))
                        <div class="card-footer bg-white border-top">
                            <form method="POST" action="{{ route('admin.listings.import.commit') }}" class="d-flex gap-2">
                                @csrf
                                <input type="hidden" name="import_key" value="{{ $importKey }}">
                                <button type="submit" class="btn btn-primary">Confirm Import</button>
                                <a href="{{ route('admin.listings.import') }}" class="btn btn-outline-secondary">Cancel</a>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection

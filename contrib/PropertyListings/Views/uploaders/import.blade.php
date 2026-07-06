@extends('admin.layouts.app')

@php
    $labels = [
        'header' => 'Property Header',
        'units' => 'Property Units',
        'fees' => 'Property Fees',
    ];
    $descriptions = [
        'header' => 'Upload listing-level fields only. Insert when code does not exist; update when code exists.',
        'units' => 'Upload units linked by listing code. Insert when code, floor, and unit do not exist together; otherwise update.',
        'fees' => 'Upload fees linked by listing code. Insert when code and fee_type do not exist together; otherwise update.',
    ];
    $title = ($labels[$type] ?? 'Property').' CSV Upload';
@endphp

@section('title', $title)

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Property Uploaders', 'url' => route('admin.property-uploaders.index')],
        ['label' => $title],
    ]" />

    <x-admin.page-header
        :title="$title"
        :back-route="route('admin.property-uploaders.index')"
        back-label="Back to Uploaders"
    >
        <x-slot:actions>
            <a href="{{ route('admin.property-uploaders.template', ['type' => $type]) }}" class="btn btn-outline-secondary">
                <i class="{{ admin_icon('bi-file-earmark-spreadsheet') }} me-1"></i> Download Template
            </a>
            @can('export', App\Modules\PropertyListings\Models\Listing::class)
                <a href="{{ route('admin.property-uploaders.export', ['type' => $type]) }}" class="btn btn-outline-secondary">
                    <i class="{{ admin_icon('bi-download') }} me-1"></i> Export Existing
                </a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-2 small">Upload CSV</div>
                <div class="card-body">
                    <p class="small text-muted">{{ $descriptions[$type] ?? '' }}</p>
                    <p class="small text-muted">
                        The uploaded columns must exactly match the template. Dropdown-backed fields must use maintained
                        dropdown codes, not display labels.
                    </p>

                    <form method="POST"
                          action="{{ route('admin.property-uploaders.import.preview', ['type' => $type]) }}"
                          enctype="multipart/form-data">
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
                                <p class="fw-semibold mb-2">Fix these issues before uploading:</p>
                                <ul class="mb-0 ps-3">
                                    @foreach ($preview['errors'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (! empty($preview['warnings']))
                            <div class="alert alert-info m-3 small">
                                <p class="fw-semibold mb-2">Warnings:</p>
                                <ul class="mb-0 ps-3">
                                    @foreach ($preview['warnings'] as $warning)
                                        <li>{{ $warning }}</li>
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
                                    @foreach ($preview['rows'] ?? [] as $rowIndex => $row)
                                        @php
                                            $rowNumber = $rowIndex + 2;
                                            $rowFieldErrors = $preview['field_errors'][$rowNumber] ?? [];
                                            $rowFieldWarnings = $preview['field_warnings'][$rowNumber] ?? [];
                                        @endphp
                                        <tr @class(['table-danger' => ! empty($rowFieldErrors), 'table-warning' => empty($rowFieldErrors) && ! empty($rowFieldWarnings)])>
                                            @foreach ($preview['headers'] ?? [] as $header)
                                                @php
                                                    $cellErrors = $rowFieldErrors[$header] ?? [];
                                                    $cellWarnings = $rowFieldWarnings[$header] ?? [];
                                                    $cellMessages = $cellErrors !== [] ? $cellErrors : $cellWarnings;
                                                @endphp
                                                <td @class([
                                                        'small',
                                                        'table-danger text-danger fw-semibold' => $cellErrors !== [],
                                                        'table-warning text-warning-emphasis fw-semibold' => $cellErrors === [] && $cellWarnings !== [],
                                                    ])
                                                    @if ($cellMessages !== []) title="{{ implode(' ', $cellMessages) }}" @endif>
                                                    <span>{{ $row[$header] ?? '' }}</span>
                                                    @if ($cellErrors !== [])
                                                        <span class="text-danger" aria-label="Field has errors">*</span>
                                                    @elseif ($cellWarnings !== [])
                                                        <span class="text-warning-emphasis" aria-label="Field has warnings">*</span>
                                                    @endif
                                                </td>
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
                    @if (! empty($importKey) && empty($preview['errors']))
                        <div class="card-footer bg-white border-top">
                            <form method="POST" action="{{ route('admin.property-uploaders.import.commit', ['type' => $type]) }}" class="d-flex gap-2">
                                @csrf
                                <input type="hidden" name="import_key" value="{{ $importKey }}">
                                <button type="submit" class="btn btn-primary">Confirm Import</button>
                                <a href="{{ route('admin.property-uploaders.import', ['type' => $type]) }}" class="btn btn-outline-secondary">Cancel</a>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection

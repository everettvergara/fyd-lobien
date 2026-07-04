@php
    $preview = $preview ?? [];
    $formatter = $preview['formatter'] ?? 'unformatted';
    $fields = $preview['fields'] ?? [];
    $rows = $preview['rows'] ?? [];
@endphp

@if ($rows === [])
    <div class="text-muted small">No published content matches the current configuration.</div>
@elseif ($formatter === 'table')
    <div class="table-responsive">
        <table class="table table-sm table-bordered content-block-preview-table mb-0">
            <thead>
                <tr>
                    @foreach ($fields as $field)
                        <th class="small">{{ $field['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td class="small">
                                @include('contentblocks::content-blocks._preview-cell', ['cell' => $cell])
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif ($formatter === 'ol' || $formatter === 'ul')
    @php $listTag = $formatter === 'ol' ? 'ol' : 'ul'; @endphp
    <{{ $listTag }} class="content-block-preview-list mb-0 ps-3">
        @foreach ($rows as $row)
            <li class="mb-2">
                @foreach ($row as $cell)
                    <div class="small">
                        <span class="text-muted">{{ $cell['label'] }}:</span>
                        @include('contentblocks::content-blocks._preview-cell', ['cell' => $cell])
                    </div>
                @endforeach
            </li>
        @endforeach
    </{{ $listTag }}>
@else
    <div class="content-block-preview-unformatted">
        @foreach ($rows as $row)
            <div class="border rounded p-2 mb-2">
                @foreach ($row as $cell)
                    <div class="small mb-1">
                        @include('contentblocks::content-blocks._preview-cell', ['cell' => $cell])
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endif

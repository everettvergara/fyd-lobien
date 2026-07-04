@php
    $sql = $sql ?? ['countSql' => '', 'dataSql' => ''];
@endphp

@if (($sql['countSql'] ?? '') === '' && ($sql['dataSql'] ?? '') === '')
    <span class="text-muted">Click Retrieve to generate SQL for the current configuration.</span>
@else
    <pre class="mb-0 small bg-white border rounded p-2"><code>-- Count query
{{ $sql['countSql'] ?? '' }}

-- Data query
{{ $sql['dataSql'] ?? '' }}</code></pre>
@endif

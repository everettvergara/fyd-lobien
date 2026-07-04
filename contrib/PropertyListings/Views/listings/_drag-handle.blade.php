@if (! empty($header))
    <th class="listing-drag-col" style="width:2rem;" title="Drag to reorder" aria-label="Drag to reorder">
        <i class="bi bi-grip-vertical fs-6 text-secondary" aria-hidden="true"></i>
    </th>
@else
    <td class="listing-drag-handle" title="Drag to reorder">
        <i class="bi bi-grip-vertical fs-5" aria-hidden="true"></i>
        <span class="visually-hidden">Drag to reorder</span>
    </td>
@endif

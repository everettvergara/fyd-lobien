@props(['colspan' => 1, 'message' => 'No records found.'])

<tr>
    <td colspan="{{ $colspan }}" class="text-center text-muted py-4">{{ $message }}</td>
</tr>

@props(['result'])

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="small text-muted">
        Showing {{ $result->records->firstItem() }} to {{ $result->records->lastItem() }} of {{ $result->records->total() }} records
    </div>
    <div>
        {{ $result->records->links('vendor.pagination.admin') }}
    </div>
</div>

<?php

namespace App\Framework\Admin\List;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AdminBulkActionService
{
    public function execute(AdminListDefinition $definition, Request $request): int
    {
        $action = $definition->bulkAction((string) $request->input('bulk_action'));

        if (! $action) {
            throw ValidationException::withMessages(['bulk_action' => 'Select a valid bulk action.']);
        }

        $ids = array_values(array_filter((array) $request->input('selected', [])));

        if ($ids === []) {
            throw ValidationException::withMessages(['selected' => 'Select at least one record.']);
        }

        $records = $definition->modelClass::query()
            ->whereKey($ids)
            ->get();

        if ($records->isEmpty()) {
            throw ValidationException::withMessages(['selected' => 'Selected records were not found.']);
        }

        if ($action->ability) {
            foreach ($records as $record) {
                Gate::authorize($action->ability, $record);
            }
        }

        if ($action->hasInput() && ! $request->filled($action->inputName)) {
            throw ValidationException::withMessages([
                $action->inputName => 'Select a value for this bulk action.',
            ]);
        }

        return $action->handle($records, $request);
    }
}

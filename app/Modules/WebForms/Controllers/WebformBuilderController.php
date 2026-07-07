<?php

namespace App\Modules\WebForms\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WebForms\Models\Webform;
use App\Modules\WebForms\Requests\UpdateWebformSchemaRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WebformBuilderController extends Controller
{
    public function edit(Webform $webform): View
    {
        $this->authorize('update', $webform);

        $schema = old('schema');

        if (is_string($schema)) {
            $schema = json_decode($schema, true);
        }

        $schemaJson = json_encode(
            is_array($schema) ? $schema : ($webform->schema ?? Webform::defaultSchema()),
            JSON_THROW_ON_ERROR
        );

        return view('webforms::webforms.builder', [
            'webform' => $webform,
            'schemaJson' => $schemaJson,
            'fieldTypes' => [
                'text', 'email', 'tel', 'number', 'textarea', 'select', 'radio', 'checkbox', 'date', 'datetime', 'hidden',
            ],
        ]);
    }

    public function update(UpdateWebformSchemaRequest $request, Webform $webform): RedirectResponse
    {
        $webform->update([
            'schema' => $request->validated('schema'),
        ]);

        ActivityLogger::log('webforms', 'schema_updated', $webform);

        return redirect('/admin/webforms/'.$webform->id.'/builder')
            ->with('success', 'Form fields saved.');
    }
}

<?php

namespace App\Modules\WebForms\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WebForms\Models\Webform;
use App\Modules\WebForms\Requests\StoreWebformRequest;
use App\Modules\WebForms\Requests\UpdateWebformRequest;
use App\Modules\WebForms\Services\WebformAdminListService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebformController extends Controller
{
    public function __construct(
        protected WebformAdminListService $list,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Webform::class);

        return view('webforms::webforms.index', [
            'list' => $this->list->result($request),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Webform::class);

        return view('webforms::webforms.create');
    }

    public function store(StoreWebformRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $webform = Webform::create([
            ...$validated,
            'schema' => Webform::defaultSchema(),
        ]);

        ActivityLogger::log('webforms', 'created', $webform);

        return redirect()
            ->route('admin.webforms.builder', $webform)
            ->with('success', 'Webform created. Add fields in the builder.');
    }

    public function edit(Webform $webform): View
    {
        $this->authorize('update', $webform);

        return view('webforms::webforms.edit', [
            'webform' => $webform,
        ]);
    }

    public function update(UpdateWebformRequest $request, Webform $webform): RedirectResponse
    {
        $webform->update($request->validated());

        ActivityLogger::log('webforms', 'updated', $webform);

        return redirect()->route('admin.webforms.index')->with('success', 'Webform updated.');
    }

    public function destroy(Webform $webform): RedirectResponse
    {
        $this->authorize('delete', $webform);

        ActivityLogger::log('webforms', 'deleted', $webform);
        $webform->delete();

        return redirect()->route('admin.webforms.index')->with('success', 'Webform deleted.');
    }
}

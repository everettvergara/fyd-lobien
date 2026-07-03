<?php

namespace App\Modules\WebForms\Controllers;

use App\Framework\Admin\List\AdminBulkActionService;
use App\Http\Controllers\Controller;
use App\Modules\WebForms\Models\WebformSubmission;
use App\Modules\WebForms\Services\WebformSubmissionAdminListService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebformSubmissionController extends Controller
{
    public function __construct(
        protected WebformSubmissionAdminListService $list,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WebformSubmission::class);

        return view('webforms::submissions.index', [
            'list' => $this->list->result($request),
        ]);
    }

    public function show(WebformSubmission $webformSubmission): View
    {
        $this->authorize('view', $webformSubmission);

        $webformSubmission->load('webform');

        return view('webforms::submissions.show', [
            'submission' => $webformSubmission,
            'webformFilter' => request('webform'),
        ]);
    }

    public function destroy(Request $request, WebformSubmission $webformSubmission): RedirectResponse
    {
        $this->authorize('delete', $webformSubmission);

        ActivityLogger::log('webforms', 'submission_deleted', $webformSubmission);
        $webformSubmission->delete();

        return redirect()
            ->route('admin.webform-submissions.index', array_filter([
                'webform' => $request->input('webform'),
            ]))
            ->with('success', 'Submission deleted.');
    }

    public function bulkDestroy(Request $request, AdminBulkActionService $bulkActions): RedirectResponse
    {
        $this->authorize('viewAny', WebformSubmission::class);

        $count = $bulkActions->execute($this->list->definition(), $request);

        return redirect()
            ->route('admin.webform-submissions.index', array_filter([
                'webform' => $request->input('webform'),
            ]))
            ->with('success', "{$count} submission(s) deleted.");
    }
}

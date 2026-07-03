<?php

namespace App\Modules\Careers\Controllers;

use App\Framework\Admin\List\AdminBulkActionService;
use App\Http\Controllers\Controller;
use App\Modules\Careers\Models\CareerApplication;
use App\Modules\Careers\Services\CareerApplicationAdminListService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CareerApplicationController extends Controller
{
    public function __construct(
        protected CareerApplicationAdminListService $list,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CareerApplication::class);

        return view('careers::career-applications.index', [
            'list' => $this->list->result($request),
        ]);
    }

    public function show(CareerApplication $careerApplication): View
    {
        $this->authorize('view', $careerApplication);

        $careerApplication->load('job');

        return view('careers::career-applications.show', [
            'application' => $careerApplication,
            'jobFilter' => request('job'),
        ]);
    }

    public function downloadResume(CareerApplication $careerApplication): StreamedResponse
    {
        $this->authorize('view', $careerApplication);

        if (! Storage::disk('local')->exists($careerApplication->resume_path)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $careerApplication->resume_path,
            $careerApplication->resume_original_filename,
        );
    }

    public function destroy(Request $request, CareerApplication $careerApplication): RedirectResponse
    {
        $this->authorize('delete', $careerApplication);

        if (Storage::disk('local')->exists($careerApplication->resume_path)) {
            Storage::disk('local')->delete($careerApplication->resume_path);
        }

        ActivityLogger::log('careers.applications', 'deleted', $careerApplication);
        $careerApplication->delete();

        return redirect()
            ->route('admin.career-applications.index', array_filter([
                'job' => $request->input('job'),
            ]))
            ->with('success', 'Application deleted.');
    }

    public function bulkDestroy(Request $request, AdminBulkActionService $bulkActions): RedirectResponse
    {
        $this->authorize('viewAny', CareerApplication::class);

        $count = $bulkActions->execute($this->list->definition(), $request);

        return redirect()
            ->route('admin.career-applications.index', array_filter([
                'job' => $request->input('job'),
            ]))
            ->with('success', "{$count} application(s) deleted.");
    }
}

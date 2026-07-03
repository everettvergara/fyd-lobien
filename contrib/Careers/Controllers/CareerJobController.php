<?php

namespace App\Modules\Careers\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Careers\Models\CareerJob;
use App\Modules\Careers\Requests\StoreCareerJobRequest;
use App\Modules\Careers\Requests\UpdateCareerJobRequest;
use App\Modules\Careers\Services\CareerJobAdminListService;
use App\Services\ActivityLogger;
use App\Services\Media\MediaUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareerJobController extends Controller
{
    public function __construct(
        protected CareerJobAdminListService $list,
        protected MediaUsageService $mediaUsage,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CareerJob::class);

        return view('careers::career-jobs.index', [
            'list' => $this->list->result($request),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CareerJob::class);

        return view('careers::career-jobs.create');
    }

    public function store(StoreCareerJobRequest $request): RedirectResponse
    {
        $job = CareerJob::create($request->validated());

        $this->syncMediaUsage($job);

        ActivityLogger::log('careers.jobs', 'created', $job);

        return redirect()->route('admin.career-jobs.index')->with('success', 'Job listing created.');
    }

    public function edit(CareerJob $careerJob): View
    {
        $this->authorize('update', $careerJob);

        return view('careers::career-jobs.edit', [
            'job' => $careerJob,
        ]);
    }

    public function update(UpdateCareerJobRequest $request, CareerJob $careerJob): RedirectResponse
    {
        $careerJob->update($request->validated());

        $this->syncMediaUsage($careerJob);

        ActivityLogger::log('careers.jobs', 'updated', $careerJob);

        return redirect()->route('admin.career-jobs.index')->with('success', 'Job listing updated.');
    }

    public function destroy(CareerJob $careerJob): RedirectResponse
    {
        $this->authorize('delete', $careerJob);

        ActivityLogger::log('careers.jobs', 'deleted', $careerJob);
        $careerJob->delete();

        return redirect()->route('admin.career-jobs.index')->with('success', 'Job listing deleted.');
    }

    protected function syncMediaUsage(CareerJob $job): void
    {
        $this->mediaUsage->syncModel($job, 'careers', [
            'picture_media_id' => 'Job Picture',
        ]);
    }
}

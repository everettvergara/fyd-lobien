<?php

namespace App\Modules\Careers\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Careers\Requests\SubmitCareerApplicationRequest;
use App\Modules\Careers\Services\CareerApplicationService;
use App\Modules\Careers\Services\CareerPublicService;
use App\Support\PublicSeo;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PublicCareerController extends Controller
{
    public function __construct(
        protected CareerPublicService $publicService,
        protected CareerApplicationService $applications,
    ) {}

    public function listJobs(): JsonResponse
    {
        $jobs = $this->publicService->listOpenJobs()
            ->map(fn ($job) => $this->publicService->toListItemDto($job))
            ->values();

        return response()->json(['jobs' => $jobs]);
    }

    public function showJob(string $slug): JsonResponse
    {
        $job = $this->publicService->findOpenJobBySlug($slug);

        if ($job === null) {
            abort(404);
        }

        return response()->json($this->publicService->toDetailDto($job));
    }

    public function showPage(string $slug): Response
    {
        $job = $this->publicService->findOpenJobBySlug($slug);

        if ($job === null) {
            abort(404);
        }

        return Inertia::render('Careers/Show', [
            'job' => $this->publicService->toDetailDto($job),
            'seo' => PublicSeo::defaults($job->title),
        ]);
    }

    public function apply(SubmitCareerApplicationRequest $request, string $slug): JsonResponse
    {
        $job = $request->job();

        if ($job === null) {
            abort(404);
        }

        $this->applications->store(
            $job,
            $request->safe()->only(['name', 'email', 'contact_number', 'remarks']),
            $request,
        );

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your application. We will be in touch if your profile matches our requirements.',
        ], 201);
    }
}

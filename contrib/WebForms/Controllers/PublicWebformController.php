<?php

namespace App\Modules\WebForms\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WebForms\Requests\SubmitWebformRequest;
use App\Modules\WebForms\Services\WebformPublicService;
use App\Modules\WebForms\Services\WebformSubmissionService;
use Illuminate\Http\JsonResponse;

class PublicWebformController extends Controller
{
    public function __construct(
        protected WebformPublicService $publicService,
        protected WebformSubmissionService $submissions,
    ) {}

    public function show(string $slug): JsonResponse
    {
        $webform = $this->publicService->findActiveBySlug($slug);

        if ($webform === null) {
            abort(404);
        }

        return response()->json($this->publicService->toPublicDto($webform));
    }

    public function submit(SubmitWebformRequest $request, string $slug): JsonResponse
    {
        $webform = $request->webform();

        if ($webform === null) {
            abort(404);
        }

        $this->submissions->store(
            $webform,
            $request->validated('fields', []),
            $request,
        );

        $settings = $webform->settings();

        return response()->json([
            'success' => true,
            'message' => $settings['success_message'] ?? 'Thank you for your submission.',
            'redirect_url' => $settings['redirect_url'] ?? null,
        ], 201);
    }
}

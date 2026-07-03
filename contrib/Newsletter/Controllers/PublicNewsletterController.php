<?php

namespace App\Modules\Newsletter\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Newsletter\Requests\SubscribeNewsletterRequest;
use App\Modules\Newsletter\Requests\UnsubscribeNewsletterRequest;
use App\Modules\Newsletter\Services\NewsletterPublicService;
use App\Modules\Newsletter\Services\NewsletterSubscriptionService;
use Illuminate\Http\JsonResponse;

class PublicNewsletterController extends Controller
{
    public function __construct(
        protected NewsletterPublicService $publicService,
        protected NewsletterSubscriptionService $subscriptions,
    ) {}

    public function show(string $slug): JsonResponse
    {
        $list = $this->publicService->findActiveBySlug($slug);

        if ($list === null) {
            abort(404);
        }

        return response()->json($this->publicService->toPublicDto($list));
    }

    public function subscribe(SubscribeNewsletterRequest $request, string $slug): JsonResponse
    {
        $list = $request->list();

        if ($list === null) {
            abort(404);
        }

        $email = auth()->check()
            ? (string) auth()->user()->email
            : (string) $request->validated('email');

        $name = auth()->check()
            ? auth()->user()->name
            : $request->validated('name');

        $this->subscriptions->subscribe(
            $list,
            $email,
            $name,
            auth()->id(),
        );

        $settings = $list->settings();

        return response()->json([
            'success' => true,
            'message' => $settings['success_subscribe'] ?? 'Thank you for subscribing.',
            'auth' => [
                'logged_in' => auth()->check(),
                'email' => $email,
                'subscribed' => true,
            ],
        ], 201);
    }

    public function unsubscribe(UnsubscribeNewsletterRequest $request, string $slug): JsonResponse
    {
        $list = $request->list();

        if ($list === null) {
            abort(404);
        }

        $email = auth()->check()
            ? (string) auth()->user()->email
            : (string) $request->validated('email');

        $unsubscribed = $this->subscriptions->unsubscribe($list, $email);

        if (! $unsubscribed) {
            return response()->json([
                'message' => 'No active subscription found for this email.',
            ], 422);
        }

        $settings = $list->settings();

        return response()->json([
            'success' => true,
            'message' => $settings['success_unsubscribe'] ?? 'You have been unsubscribed.',
            'auth' => [
                'logged_in' => auth()->check(),
                'email' => $email,
                'subscribed' => false,
            ],
        ]);
    }
}

<?php

namespace App\Modules\Newsletter\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Newsletter\Models\NewsletterList;
use App\Modules\Newsletter\Models\NewsletterSend;
use App\Modules\Newsletter\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Requests\SendNewsletterRequest;
use App\Modules\Newsletter\Services\NewsletterSendAdminListService;
use App\Modules\Newsletter\Services\NewsletterSendService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterSendController extends Controller
{
    public function __construct(
        protected NewsletterSendAdminListService $history,
        protected NewsletterSendService $sender,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', NewsletterSend::class);

        return view('newsletter::newsletter-sends.index', [
            'list' => $this->history->result($request),
        ]);
    }

    public function compose(): View
    {
        $this->authorize('send', NewsletterSend::class);

        $lists = NewsletterList::query()->orderBy('name')->get();
        $subscriberCounts = NewsletterSubscriber::query()
            ->where('status', NewsletterSubscriber::STATUS_ACTIVE)
            ->selectRaw('newsletter_list_id, COUNT(*) as total')
            ->groupBy('newsletter_list_id')
            ->pluck('total', 'newsletter_list_id');

        return view('newsletter::newsletters.compose', [
            'lists' => $lists,
            'subscriberCounts' => $subscriberCounts,
        ]);
    }

    public function send(SendNewsletterRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $list = NewsletterList::findOrFail($validated['newsletter_list_id']);

        $send = $this->sender->dispatch($list, $validated['subject'], $validated['body'], $request->user());

        return redirect()
            ->route('admin.newsletter-sends.index')
            ->with('success', "Newsletter queued for {$send->recipient_count} subscriber(s).");
    }
}

<?php

namespace App\Modules\Newsletter\Controllers;

use App\Framework\Admin\List\AdminBulkActionService;
use App\Http\Controllers\Controller;
use App\Modules\Newsletter\Models\NewsletterList;
use App\Modules\Newsletter\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Requests\StoreNewsletterSubscriberRequest;
use App\Modules\Newsletter\Requests\UpdateNewsletterSubscriberRequest;
use App\Modules\Newsletter\Services\NewsletterSubscriberAdminListService;
use App\Modules\Newsletter\Services\NewsletterSubscriberExportService;
use App\Modules\Newsletter\Services\NewsletterSubscriptionService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterSubscriberController extends Controller
{
    public function __construct(
        protected NewsletterSubscriberAdminListService $list,
        protected NewsletterSubscriptionService $subscriptions,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', NewsletterSubscriber::class);

        return view('newsletter::newsletter-subscribers.index', [
            'list' => $this->list->result($request),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', NewsletterSubscriber::class);

        return view('newsletter::newsletter-subscribers.create', [
            'lists' => NewsletterList::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreNewsletterSubscriberRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $list = NewsletterList::findOrFail($validated['newsletter_list_id']);

        if ($validated['status'] === NewsletterSubscriber::STATUS_ACTIVE) {
            $profile = [
                'name' => $validated['name'] ?? null,
                'mobile_number' => $validated['mobile_number'] ?? null,
                'designation' => $validated['designation'] ?? null,
                'company' => $validated['company'] ?? null,
            ];

            $subscriber = $this->subscriptions->subscribe(
                $list,
                $validated['email'],
                $profile,
                null,
                false,
            );
        } else {
            $subscriber = NewsletterSubscriber::create([
                ...$validated,
                'unsubscribe_token' => Str::random(64),
                'subscribed_at' => now(),
                'unsubscribed_at' => now(),
            ]);
        }

        ActivityLogger::log('newsletter', 'subscriber_created', $subscriber);

        return redirect()->route('admin.newsletter-subscribers.index')->with('success', 'Subscriber created.');
    }

    public function edit(NewsletterSubscriber $newsletterSubscriber): View
    {
        $this->authorize('update', $newsletterSubscriber);

        return view('newsletter::newsletter-subscribers.edit', [
            'subscriber' => $newsletterSubscriber,
            'lists' => NewsletterList::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateNewsletterSubscriberRequest $request, NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $validated = $request->validated();

        if ($validated['status'] === NewsletterSubscriber::STATUS_ACTIVE && ! $newsletterSubscriber->isActive()) {
            $validated['subscribed_at'] = now();
            $validated['unsubscribed_at'] = null;
        }

        if ($validated['status'] === NewsletterSubscriber::STATUS_UNSUBSCRIBED && $newsletterSubscriber->isActive()) {
            $validated['unsubscribed_at'] = now();
        }

        $newsletterSubscriber->update($validated);

        ActivityLogger::log('newsletter', 'subscriber_updated', $newsletterSubscriber);

        return redirect()->route('admin.newsletter-subscribers.index')->with('success', 'Subscriber updated.');
    }

    public function destroy(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $this->authorize('delete', $newsletterSubscriber);

        ActivityLogger::log('newsletter', 'subscriber_deleted', $newsletterSubscriber);
        $newsletterSubscriber->delete();

        return redirect()->route('admin.newsletter-subscribers.index')->with('success', 'Subscriber deleted.');
    }

    public function export(Request $request, NewsletterSubscriberExportService $export): StreamedResponse
    {
        $this->authorize('export', NewsletterSubscriber::class);

        return $export->download($request);
    }

    public function bulkDestroy(Request $request, AdminBulkActionService $bulkActions): RedirectResponse
    {
        $this->authorize('viewAny', NewsletterSubscriber::class);

        $count = $bulkActions->execute($this->list->definition(), $request);

        return redirect()
            ->route('admin.newsletter-subscribers.index', $request->only(['search', 'newsletter_list', 'status']))
            ->with('success', "{$count} subscriber(s) processed.");
    }
}

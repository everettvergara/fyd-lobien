<?php

namespace App\Modules\Newsletter\Services;

use App\Framework\Admin\List\AdminListState;
use App\Modules\Newsletter\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterSubscriberExportService
{
    public function __construct(
        protected NewsletterSubscriberAdminListService $listService,
    ) {}

    public function download(Request $request): StreamedResponse
    {
        $definition = $this->listService->definition();
        $state = AdminListState::fromRequest($request, $definition);

        $query = NewsletterSubscriber::query()
            ->with('list')
            ->leftJoin('newsletter_lists', 'newsletter_lists.id', '=', 'newsletter_subscribers.newsletter_list_id')
            ->select('newsletter_subscribers.*');

        $this->applySearch($query, $definition, $state);
        $this->applyFilters($query, $definition, $state);
        $query->orderBy('newsletter_subscribers.subscribed_at', 'desc');

        $filename = 'newsletter-subscribers-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'List', 'Email', 'Name', 'Mobile Number', 'Designation', 'Company', 'Status', 'Subscribed At', 'Unsubscribed At']);

            $query->chunk(500, function ($subscribers) use ($handle) {
                foreach ($subscribers as $subscriber) {
                    fputcsv($handle, [
                        $subscriber->id,
                        $subscriber->list?->name,
                        $subscriber->email,
                        $subscriber->name,
                        $subscriber->mobile_number,
                        $subscriber->designation,
                        $subscriber->company,
                        $subscriber->status,
                        $subscriber->subscribed_at?->format('Y-m-d H:i:s'),
                        $subscriber->unsubscribed_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function applySearch(Builder $query, $definition, AdminListState $state): void
    {
        if (! $state->search) {
            return;
        }

        $search = $state->search;

        $query->where(function (Builder $query) use ($search) {
            $query->where('newsletter_subscribers.email', 'like', "%{$search}%")
                ->orWhere('newsletter_subscribers.name', 'like', "%{$search}%")
                ->orWhere('newsletter_subscribers.mobile_number', 'like', "%{$search}%")
                ->orWhere('newsletter_subscribers.designation', 'like', "%{$search}%")
                ->orWhere('newsletter_subscribers.company', 'like', "%{$search}%");
        });
    }

    protected function applyFilters(Builder $query, $definition, AdminListState $state): void
    {
        foreach ($state->activeFilters() as $key => $value) {
            $definition->filter($key)?->apply($query, $value);
        }
    }
}

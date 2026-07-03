<?php

namespace App\Modules\Newsletter\Services;

use App\Framework\Admin\List\AdminBulkAction;
use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListFilter;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\Newsletter\Models\NewsletterList;
use App\Modules\Newsletter\Models\NewsletterSubscriber;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class NewsletterSubscriberAdminListService
{
    public function __construct(
        protected AdminListService $lists,
        protected NewsletterSubscriptionService $subscriptions,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = NewsletterSubscriber::query()
            ->with('list')
            ->leftJoin('newsletter_lists', 'newsletter_lists.id', '=', 'newsletter_subscribers.newsletter_list_id')
            ->select('newsletter_subscribers.*');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'newsletter-subscribers',
            title: 'Subscribers',
            modelClass: NewsletterSubscriber::class,
            columns: [
                AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('id', 'ID', sortField: 'newsletter_subscribers.id', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('list_name', 'List', fn (NewsletterSubscriber $subscriber) => $subscriber->list
                    ? e($subscriber->list->name)
                    : '<span class="text-muted">—</span>', sortField: 'newsletter_lists.name', raw: true),
                AdminListColumn::make('email', 'Email', sortField: 'newsletter_subscribers.email'),
                AdminListColumn::make('name', 'Name', fn (NewsletterSubscriber $subscriber) => e($subscriber->name ?? '—'), sortField: 'newsletter_subscribers.name', class: 'small text-muted'),
                AdminListColumn::make('status', 'Status', fn (NewsletterSubscriber $subscriber) => $subscriber->isActive()
                    ? '<span class="badge text-bg-success">Active</span>'
                    : '<span class="badge text-bg-secondary">Unsubscribed</span>', sortField: 'newsletter_subscribers.status', raw: true),
                AdminListColumn::make('subscribed_at', 'Subscribed', fn (NewsletterSubscriber $subscriber) => $subscriber->subscribed_at?->format('Y-m-d H:i') ?? '—', sortField: 'newsletter_subscribers.subscribed_at', class: 'small'),
            ],
            filters: [
                AdminListFilter::make(
                    'newsletter_list',
                    'List',
                    'select',
                    fn () => NewsletterList::query()->orderBy('name')->pluck('name', 'id')->all(),
                    fn (Builder $query, mixed $value) => $query->where('newsletter_subscribers.newsletter_list_id', $value),
                ),
                AdminListFilter::make(
                    'status',
                    'Status',
                    'select',
                    fn () => [
                        NewsletterSubscriber::STATUS_ACTIVE => 'Active',
                        NewsletterSubscriber::STATUS_UNSUBSCRIBED => 'Unsubscribed',
                    ],
                    fn (Builder $query, mixed $value) => $query->where('newsletter_subscribers.status', $value),
                ),
            ],
            rowActions: [
                AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (NewsletterSubscriber $subscriber) => route('admin.newsletter-subscribers.edit', $subscriber), ability: 'update'),
                AdminListAction::make('delete', 'Delete', 'bi-trash', fn (NewsletterSubscriber $subscriber) => route('admin.newsletter-subscribers.destroy', $subscriber), method: 'DELETE', ability: 'delete', confirm: 'Delete this subscriber?', danger: true),
            ],
            bulkActions: [
                AdminBulkAction::make('delete', 'Delete selected', 'delete', fn (Collection $subscribers) => $this->bulkDelete($subscribers), 'Delete selected subscribers?', danger: true),
                AdminBulkAction::make('unsubscribe', 'Unsubscribe selected', 'unsubscribe', fn (Collection $subscribers) => $this->bulkUnsubscribe($subscribers), 'Unsubscribe selected subscribers?'),
            ],
            searchFields: ['email', 'name'],
            defaultSort: 'subscribed_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
        );
    }

    protected function bulkDelete(Collection $subscribers): int
    {
        $subscribers->each(function (NewsletterSubscriber $subscriber) {
            ActivityLogger::log('newsletter', 'subscriber_deleted', $subscriber);
            $subscriber->delete();
        });

        return $subscribers->count();
    }

    protected function bulkUnsubscribe(Collection $subscribers): int
    {
        $count = 0;

        $subscribers->load('list');

        $subscribers->each(function (NewsletterSubscriber $subscriber) use (&$count) {
            if (! $subscriber->isActive() || $subscriber->list === null) {
                return;
            }

            if ($this->subscriptions->unsubscribe($subscriber->list, $subscriber->email)) {
                ActivityLogger::log('newsletter', 'subscriber_unsubscribed', $subscriber);
                $count++;
            }
        });

        return $count;
    }
}

<?php

namespace App\Modules\Newsletter\Services;

use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\Newsletter\Models\NewsletterSend;
use Illuminate\Http\Request;

class NewsletterSendAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = NewsletterSend::query()
            ->with(['list', 'sender'])
            ->leftJoin('newsletter_lists', 'newsletter_lists.id', '=', 'newsletter_sends.newsletter_list_id')
            ->select('newsletter_sends.*');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'newsletter-sends',
            title: 'Send History',
            modelClass: NewsletterSend::class,
            columns: [
                AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('id', 'ID', sortField: 'newsletter_sends.id', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('list_name', 'List', fn (NewsletterSend $send) => $send->list
                    ? e($send->list->name)
                    : '<span class="text-muted">—</span>', sortField: 'newsletter_lists.name', raw: true),
                AdminListColumn::make('subject', 'Subject', sortField: 'newsletter_sends.subject'),
                AdminListColumn::make('recipient_count', 'Recipients', sortField: 'newsletter_sends.recipient_count', class: 'small'),
                AdminListColumn::make('progress', 'Progress', fn (NewsletterSend $send) => e((string) ($send->sent_count + $send->failed_count).' / '.$send->recipient_count), class: 'small'),
                AdminListColumn::make('status', 'Status', fn (NewsletterSend $send) => e(ucfirst(str_replace('_', ' ', (string) $send->status))), sortField: 'newsletter_sends.status', class: 'small'),
                AdminListColumn::make('queued_at', 'Queued', fn (NewsletterSend $send) => $send->queued_at?->format('Y-m-d H:i') ?? '—', sortField: 'newsletter_sends.queued_at', class: 'small'),
                AdminListColumn::make('completed_at', 'Completed', fn (NewsletterSend $send) => $send->completed_at?->format('Y-m-d H:i') ?? '—', sortField: 'newsletter_sends.completed_at', class: 'small'),
            ],
            searchFields: ['subject'],
            defaultSort: 'queued_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
        );
    }
}

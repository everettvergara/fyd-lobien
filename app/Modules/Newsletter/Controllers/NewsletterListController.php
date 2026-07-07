<?php

namespace App\Modules\Newsletter\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Newsletter\Models\NewsletterList;
use App\Modules\Newsletter\Requests\StoreNewsletterListRequest;
use App\Modules\Newsletter\Requests\UpdateNewsletterListRequest;
use App\Modules\Newsletter\Services\NewsletterListAdminListService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterListController extends Controller
{
    public function __construct(
        protected NewsletterListAdminListService $list,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', NewsletterList::class);

        return view('newsletter::newsletter-lists.index', [
            'list' => $this->list->result($request),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', NewsletterList::class);

        return view('newsletter::newsletter-lists.create');
    }

    public function store(StoreNewsletterListRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $settings = $validated['settings'] ?? [];
        unset($validated['settings']);

        $newsletterList = NewsletterList::create([
            ...$validated,
            'settings' => array_merge(
                NewsletterList::defaultSettings(),
                NewsletterList::normalizeSettingsInput($settings),
            ),
        ]);

        ActivityLogger::log('newsletter', 'list_created', $newsletterList);

        return redirect()->route('admin.newsletter-lists.index')->with('success', 'Newsletter list created.');
    }

    public function edit(NewsletterList $newsletterList): View
    {
        $this->authorize('update', $newsletterList);

        return view('newsletter::newsletter-lists.edit', [
            'newsletterList' => $newsletterList,
        ]);
    }

    public function update(UpdateNewsletterListRequest $request, NewsletterList $newsletterList): RedirectResponse
    {
        $validated = $request->validated();
        $settings = $validated['settings'] ?? [];
        unset($validated['settings']);

        $newsletterList->update([
            ...$validated,
            'settings' => array_merge(
                NewsletterList::defaultSettings(),
                NewsletterList::normalizeSettingsInput($settings),
            ),
        ]);

        ActivityLogger::log('newsletter', 'list_updated', $newsletterList);

        return redirect()->route('admin.newsletter-lists.index')->with('success', 'Newsletter list updated.');
    }

    public function destroy(NewsletterList $newsletterList): RedirectResponse
    {
        $this->authorize('delete', $newsletterList);

        ActivityLogger::log('newsletter', 'list_deleted', $newsletterList);
        $newsletterList->delete();

        return redirect()->route('admin.newsletter-lists.index')->with('success', 'Newsletter list deleted.');
    }
}

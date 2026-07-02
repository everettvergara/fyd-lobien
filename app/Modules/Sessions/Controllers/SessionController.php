<?php

namespace App\Modules\Sessions\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DatabaseSession;
use App\Modules\Sessions\Services\SessionAdminListService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function __construct(
        protected SessionAdminListService $sessionList,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DatabaseSession::class);

        return view('sessions::sessions.index', [
            'list' => $this->sessionList->result($request),
        ]);
    }

    public function destroy(DatabaseSession $session): RedirectResponse
    {
        $this->authorize('delete', $session);

        $sessionId = $session->id;
        $userId = $session->user_id;

        $session->delete();

        ActivityLogger::log('sessions', 'revoked', null, [
            'session_id' => $sessionId,
            'user_id' => $userId,
        ]);

        return back()->with('success', 'Session revoked successfully.');
    }
}

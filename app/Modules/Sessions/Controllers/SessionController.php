<?php

namespace App\Modules\Sessions\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DatabaseSession;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', DatabaseSession::class);

        $query = DatabaseSession::with('user')->orderByDesc('last_activity');

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        $sessions = $query->paginate(25)->withQueryString();

        return view('sessions::sessions.index', [
            'sessions' => $sessions,
            'filters' => $request->only(['user_id']),
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

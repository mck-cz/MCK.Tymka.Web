<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\Event;
use App\Models\EventResult;
use App\Models\TeamMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventResultController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $clubId = session('current_club_id');
        abort_unless($event->club_id === $clubId, 403);
        $this->authorizeCoachOrAdmin($event);

        $validated = $request->validate([
            'score_home' => 'nullable|integer|min:0',
            'score_away' => 'nullable|integer|min:0',
            'opponent_name' => 'nullable|string|max:255',
            'result' => 'nullable|in:win,loss,draw',
            'notes' => 'nullable|string|max:1000',
        ]);

        EventResult::updateOrCreate(
            ['event_id' => $event->id],
            [
                ...$validated,
                'recorded_by' => Auth::id(),
            ]
        );

        return back()->with('success', __('messages.results.saved'));
    }

    public function destroy(EventResult $eventResult)
    {
        $clubId = session('current_club_id');
        abort_unless($eventResult->event->club_id === $clubId, 403);
        $this->authorizeCoachOrAdmin($eventResult->event);

        $eventResult->delete();

        return back()->with('success', __('messages.results.deleted'));
    }

    private function authorizeCoachOrAdmin(Event $event): void
    {
        $clubId = session('current_club_id');
        $userId = Auth::id();

        $isAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        if ($isAdmin) return;

        $isCoach = TeamMembership::where('team_id', $event->team_id)
            ->where('user_id', $userId)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->exists();

        abort_unless($isCoach, 403);
    }
}

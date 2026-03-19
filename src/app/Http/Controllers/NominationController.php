<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClubMembership;
use App\Models\Event;
use App\Models\Nomination;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NominationController extends Controller
{
    public function manage(Event $event)
    {
        $clubId = session('current_club_id');

        abort_unless($event->club_id === $clubId, 403);
        $this->authorizeCoachOrAdmin($event);
        abort_unless(in_array($event->event_type, ['match', 'tournament', 'competition']), 403);

        $event->load(['team', 'nominations.teamMembership.user', 'nominations.sourceTeam']);

        // Get athletes from ALL teams in this club (for cross-team nomination)
        $clubTeams = Team::where('club_id', $clubId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $allAthletes = TeamMembership::whereIn('team_id', $clubTeams->keys())
            ->where('status', 'active')
            ->where('role', 'athlete')  // Only athletes, not coaches
            ->with(['user', 'team'])
            ->get();

        // Filter out already nominated members (by user_id, not membership_id — same person can be in multiple teams)
        $nominatedUserIds = $event->nominations
            ->pluck('teamMembership.user_id')
            ->toArray();

        $availableMembers = $allAthletes->filter(
            fn ($member) => !in_array($member->user_id, $nominatedUserIds)
        );

        // Group available members by team, event's own team first
        $membersByTeam = $availableMembers->groupBy('team_id')
            ->sortBy(fn ($members, $teamId) => $teamId === $event->team_id ? 0 : 1);

        return view('events.nominations', [
            'event' => $event,
            'membersByTeam' => $membersByTeam,
            'clubTeams' => $clubTeams,
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $clubId = session('current_club_id');

        abort_unless($event->club_id === $clubId, 403);
        $this->authorizeCoachOrAdmin($event);

        $validated = $request->validate([
            'team_membership_ids' => 'required|array',
            'team_membership_ids.*' => 'exists:team_memberships,id',
        ]);

        // Load memberships to get their source team_id
        $memberships = TeamMembership::whereIn('id', $validated['team_membership_ids'])->get();

        foreach ($memberships as $membership) {
            $exists = Nomination::where('event_id', $event->id)
                ->where('team_membership_id', $membership->id)
                ->exists();

            if (!$exists) {
                Nomination::create([
                    'event_id' => $event->id,
                    'team_membership_id' => $membership->id,
                    'source_team_id' => $membership->team_id,
                    'status' => 'nominated',
                    'priority' => 1,
                    'nominated_by' => Auth::id(),
                ]);
            }
        }

        // Notify each nominated user
        $nominatedUserIds = $memberships
            ->pluck('user_id')
            ->reject(fn ($id) => $id === Auth::id())
            ->unique()
            ->values()
            ->toArray();

        if (!empty($nominatedUserIds)) {
            NotificationService::send(
                $nominatedUserIds,
                'nomination',
                __('messages.notifications_msg.nomination', ['title' => $event->title])
            );
        }

        return redirect()->back()->with('success', __('messages.nominations.added'));
    }

    public function respond(Request $request, Nomination $nomination)
    {
        $user = Auth::user();

        // Verify the nomination belongs to the current user or user is a guardian
        $nominationUserId = $nomination->teamMembership->user_id;
        $isOwner = $nominationUserId === $user->id;
        $isGuardian = $user->guardianOf()->where('child_id', $nominationUserId)->exists();

        abort_unless($isOwner || $isGuardian, 403);

        $validated = $request->validate([
            'status' => 'required|in:accepted,declined',
        ]);

        $nomination->update([
            'status' => $validated['status'],
            'responded_by' => Auth::id(),
            'responded_at' => now(),
        ]);

        // Sync attendance RSVP with nomination response
        $attendance = Attendance::where('event_id', $nomination->event_id)
            ->where('team_membership_id', $nomination->team_membership_id)
            ->first();

        if ($attendance) {
            $attendance->update([
                'rsvp_status' => $validated['status'] === 'accepted' ? 'confirmed' : 'declined',
                'responded_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', __('messages.nominations.updated'));
    }

    public function destroy(Nomination $nomination)
    {
        $clubId = session('current_club_id');

        // Verify belongs to current club through event
        abort_unless($nomination->event->club_id === $clubId, 403);
        $this->authorizeCoachOrAdmin($nomination->event);

        $nomination->delete();

        return redirect()->back()->with('success', __('messages.nominations.removed'));
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

        if ($isAdmin) {
            return;
        }

        $isCoach = TeamMembership::where('team_id', $event->team_id)
            ->where('user_id', $userId)
            ->where('role', 'head_coach')
            ->where('status', 'active')
            ->exists();

        abort_unless($isCoach, 403);
    }
}

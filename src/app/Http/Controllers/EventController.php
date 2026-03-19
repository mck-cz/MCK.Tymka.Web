<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\Event;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\Venue;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $clubId = session('current_club_id');

        // Club admins see all events; others see only their + children's teams
        $isClubAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        $childIds = $user->getChildrenIdsInClub($clubId);
        $relevantUserIds = collect([$user->id])->merge($childIds);

        if ($isClubAdmin) {
            $teamIds = Team::where('club_id', $clubId)->pluck('id');
        } else {
            $teamIds = TeamMembership::whereIn('user_id', $relevantUserIds)
                ->whereHas('team', fn ($q) => $q->where('club_id', $clubId))
                ->pluck('team_id');
        }

        // Build events query
        $query = Event::whereIn('team_id', $teamIds)
            ->with(['team', 'venue', 'attendances']);

        // Time filter: upcoming (default), past, all
        $timeFilter = $request->input('time', 'upcoming');
        if ($timeFilter === 'upcoming') {
            $query->whereIn('status', ['scheduled', 'completed'])
                ->where('starts_at', '>', now()->subHours(4))
                ->orderBy('starts_at', 'asc');
        } elseif ($timeFilter === 'past') {
            $query->where('starts_at', '<', now())
                ->orderBy('starts_at', 'desc');
        } else {
            $query->orderBy('starts_at', 'desc');
        }

        // Filter by event type
        if ($request->filled('event_type')) {
            $query->ofType($request->input('event_type'));
        }

        // Filter by team
        if ($request->filled('team_id')) {
            $query->forTeam($request->input('team_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $events = $query->get();

        // Get user's teams for filter dropdown
        $teams = Team::whereIn('id', $teamIds)->orderBy('name')->get();

        // Build team-to-child name mapping for parent indicators
        $teamChildMap = [];
        $eventChildRsvp = [];
        if ($childIds->isNotEmpty()) {
            $children = $user->children()
                ->whereHas('clubMemberships', fn ($q) => $q->where('club_id', $clubId)->where('status', 'active'))
                ->get();

            foreach ($children as $child) {
                $childTeamIds = TeamMembership::where('user_id', $child->id)
                    ->where('status', 'active')
                    ->whereIn('team_id', $teamIds)
                    ->pluck('team_id');

                foreach ($childTeamIds as $tid) {
                    $teamChildMap[$tid] = $teamChildMap[$tid] ?? [];
                    $teamChildMap[$tid][] = $child->first_name;
                }
            }

            // Per-event child RSVP status for badge colors
            $childAthleteIds = TeamMembership::whereIn('user_id', $childIds)
                ->whereIn('team_id', $teamIds)
                ->where('role', 'athlete')
                ->pluck('id');

            if ($childAthleteIds->isNotEmpty() && $events->isNotEmpty()) {
                $childAttendances = Attendance::whereIn('team_membership_id', $childAthleteIds)
                    ->whereIn('event_id', $events->pluck('id'))
                    ->with('teamMembership')
                    ->get();

                foreach ($childAttendances as $att) {
                    $childName = $children->firstWhere('id', $att->teamMembership->user_id)?->first_name;
                    if ($childName) {
                        $eventChildRsvp[$att->event_id][$childName] = $att->rsvp_status;
                    }
                }
            }
        }

        return view('events.index', [
            'events' => $events,
            'teams' => $teams,
            'teamChildMap' => $teamChildMap,
            'eventChildRsvp' => $eventChildRsvp,
            'selectedType' => $request->input('event_type'),
            'selectedTeamId' => $request->input('team_id'),
            'selectedTime' => $timeFilter,
            'selectedStatus' => $request->input('status'),
        ]);
    }

    public function show(Event $event)
    {
        $user = Auth::user();
        $clubId = session('current_club_id');

        // Auto-switch club if event belongs to a different club user is member of
        if ($event->club_id !== $clubId) {
            $isMemberOfEventClub = ClubMembership::where('club_id', $event->club_id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();

            // Also allow if user's child is member of the event's club
            if (!$isMemberOfEventClub) {
                $childIds = $user->children()->pluck('users.id');
                $isMemberOfEventClub = $childIds->isNotEmpty() && ClubMembership::where('club_id', $event->club_id)
                    ->whereIn('user_id', $childIds)
                    ->where('status', 'active')
                    ->exists();
            }

            abort_unless($isMemberOfEventClub, 403);

            session(['current_club_id' => $event->club_id]);
            $clubId = $event->club_id;
        }

        $event->load(['team', 'venue', 'attendances.teamMembership.user', 'nominations.teamMembership.user', 'eventComments.user', 'eventResult']);

        // Load coaches for this team
        $coaches = TeamMembership::where('team_id', $event->team_id)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->with('user')
            ->get();

        // Find current user's attendance record (only show RSVP for athletes, not coaches)
        $userAttendance = $event->attendances
            ->first(fn ($a) => $a->teamMembership->user_id === $user->id && $a->teamMembership->role === 'athlete');

        // Find children's attendance records (only athletes)
        $childIds = $user->getChildrenIdsInClub($clubId);
        $childAttendances = $event->attendances
            ->filter(fn ($a) => $childIds->contains($a->teamMembership->user_id) && $a->teamMembership->role === 'athlete');

        // Check if user can manage match results (coach or admin)
        $canManageResult = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists()
        || TeamMembership::where('team_id', $event->team_id)
            ->where('user_id', $user->id)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->exists();

        // Split attendances: coaches vs athletes
        $coachAttendances = $event->attendances->filter(
            fn ($a) => in_array($a->teamMembership->role, ['head_coach', 'assistant_coach'])
        );
        $athleteAttendances = $event->attendances->filter(
            fn ($a) => $a->teamMembership->role === 'athlete'
        );

        return view('events.show', [
            'event' => $event,
            'userAttendance' => $userAttendance,
            'childAttendances' => $childAttendances,
            'canManageResult' => $canManageResult,
            'canEdit' => $canManageResult,
            'coaches' => $coaches,
            'coachAttendances' => $coachAttendances,
            'athleteAttendances' => $athleteAttendances,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $clubId = session('current_club_id');

        $teams = $this->getManageableTeams($user, $clubId);
        $venues = Venue::where('club_id', $clubId)->orderBy('name')->get();

        return view('events.create', [
            'teams' => $teams,
            'venues' => $venues,
        ]);
    }

    public function store(Request $request)
    {
        $clubId = session('current_club_id');

        // Verify user is a coach on the submitted team or a club admin
        $user = Auth::user();
        $isClubAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();
        $isCoach = TeamMembership::where('user_id', $user->id)
            ->where('team_id', $request->input('team_id'))
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->exists();
        abort_unless($isClubAdmin || $isCoach, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_type' => 'required|in:training,match,competition,tournament',
            'team_id' => ['required', Rule::exists('teams', 'id')->where('club_id', $clubId)],
            'venue_id' => ['nullable', Rule::exists('venues', 'id')->where('club_id', $clubId)],
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'nullable|date|after:starts_at',
            'rsvp_deadline' => 'nullable|date|before:starts_at',
            'min_capacity' => 'nullable|integer|min:1',
            'max_capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'instructions' => 'nullable|string',
        ]);

        $event = Event::create([
            ...$validated,
            'club_id' => $clubId,
            'created_by' => Auth::id(),
            'status' => 'scheduled',
        ]);

        // Auto-create attendance records for all active team members
        $memberships = TeamMembership::where('team_id', $event->team_id)
            ->where('status', 'active')
            ->get();

        foreach ($memberships as $membership) {
            Attendance::create([
                'event_id' => $event->id,
                'team_membership_id' => $membership->id,
                'rsvp_status' => 'pending',
            ]);
        }

        // Notify all team members about the new event
        $memberUserIds = TeamMembership::where('team_id', $event->team_id)
            ->where('status', 'active')
            ->where('user_id', '!=', Auth::id())
            ->pluck('user_id')
            ->toArray();

        if (!empty($memberUserIds)) {
            NotificationService::send(
                $memberUserIds,
                'new_event',
                __('messages.notifications_msg.new_event', ['title' => $event->title])
            );
        }

        return redirect()->route('events.show', $event)
            ->with('success', __('messages.events.created'));
    }

    /**
     * Sync attendance records — add missing team members to the event.
     */
    public function syncAttendances(Event $event)
    {
        $clubId = session('current_club_id');
        abort_unless($event->club_id === $clubId, 403);
        $this->authorizeEventManage($event);

        $existingMembershipIds = $event->attendances()->pluck('team_membership_id');

        $missingMemberships = TeamMembership::where('team_id', $event->team_id)
            ->where('status', 'active')
            ->whereNotIn('id', $existingMembershipIds)
            ->get();

        foreach ($missingMemberships as $membership) {
            Attendance::create([
                'event_id' => $event->id,
                'team_membership_id' => $membership->id,
                'rsvp_status' => 'pending',
            ]);
        }

        $count = $missingMemberships->count();

        return back()->with('success', __('messages.events.attendances_synced', ['count' => $count]));
    }

    public function edit(Event $event)
    {
        $user = Auth::user();
        $clubId = session('current_club_id');

        abort_unless($event->club_id === $clubId, 403);
        $this->authorizeEventManage($event);

        $teams = $this->getManageableTeams($user, $clubId);
        $venues = Venue::where('club_id', $clubId)->orderBy('name')->get();

        return view('events.edit', [
            'event' => $event,
            'teams' => $teams,
            'venues' => $venues,
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $clubId = session('current_club_id');

        abort_unless($event->club_id === $clubId, 403);
        $this->authorizeEventManage($event);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_type' => 'required|in:training,match,competition,tournament',
            'team_id' => ['required', Rule::exists('teams', 'id')->where('club_id', $clubId)],
            'venue_id' => ['nullable', Rule::exists('venues', 'id')->where('club_id', $clubId)],
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'rsvp_deadline' => 'nullable|date|before:starts_at',
            'min_capacity' => 'nullable|integer|min:1',
            'max_capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'instructions' => 'nullable|string',
        ]);

        $event->update($validated);

        return redirect()->route('events.show', $event)
            ->with('success', __('messages.events.updated'));
    }

    public function cancel(Event $event)
    {
        $clubId = session('current_club_id');

        abort_unless($event->club_id === $clubId, 403);
        $this->authorizeEventManage($event);

        $event->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
        ]);

        return redirect()->route('events.index')
            ->with('success', __('messages.events.cancelled_msg'));
    }

    public function complete(Event $event)
    {
        $clubId = session('current_club_id');

        abort_unless($event->club_id === $clubId, 403);
        $this->authorizeEventManage($event);

        // Only allow completing events that are in_progress or past
        abort_unless(in_array($event->effective_status, ['in_progress', 'past']), 422);

        $event->update([
            'status' => 'completed',
            'completed_by' => Auth::id(),
            'completed_at' => now(),
        ]);

        return redirect()->route('events.show', $event)
            ->with('success', __('messages.events.completed_msg'));
    }

    public function destroy(Event $event)
    {
        $clubId = session('current_club_id');
        abort_unless($event->club_id === $clubId, 403);
        $this->authorizeEventManage($event);

        $event->attendances()->delete();
        $event->nominations()->delete();
        $event->eventComments()->delete();
        $event->eventResult()?->delete();
        $event->delete();

        return redirect()->route('events.index')
            ->with('success', __('messages.events.deleted'));
    }

    private function authorizeEventManage(Event $event): void
    {
        $clubId = session('current_club_id');
        $userId = Auth::id();

        $isClubAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        if ($isClubAdmin) {
            return;
        }

        $isCoach = TeamMembership::where('team_id', $event->team_id)
            ->where('user_id', $userId)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->exists();

        abort_unless($isCoach, 403);
    }

    private function getManageableTeams($user, $clubId)
    {
        $isClubAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        if ($isClubAdmin) {
            return Team::where('club_id', $clubId)->orderBy('name')->get();
        }

        return Team::where('club_id', $clubId)
            ->whereHas('teamMemberships', fn ($q) => $q->where('user_id', $user->id)
                ->whereIn('role', ['head_coach', 'assistant_coach']))
            ->orderBy('name')
            ->get();
    }
}

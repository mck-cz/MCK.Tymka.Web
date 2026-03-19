<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClubMembership;
use App\Models\Event;
use App\Models\MemberPayment;
use App\Models\Notification;
use App\Models\Team;
use App\Models\TeamMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Collect ALL clubs where user is a member
        $userClubIds = ClubMembership::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('club_id');

        // Also include clubs where user's children are members
        $allChildren = $user->children()->get();
        $childClubIds = collect();
        if ($allChildren->isNotEmpty()) {
            $childClubIds = ClubMembership::whereIn('user_id', $allChildren->pluck('id'))
                ->where('status', 'active')
                ->pluck('club_id');
        }
        $allClubIds = $userClubIds->merge($childClubIds)->unique();

        $clubsMap = \App\Models\Club::whereIn('id', $allClubIds)->pluck('name', 'id');
        $multiClub = $allClubIds->count() > 1;

        // For each club, determine which teams the user can see
        $teamIds = collect();
        foreach ($allClubIds as $clubId) {
            $isClubAdmin = ClubMembership::where('club_id', $clubId)
                ->where('user_id', $user->id)
                ->whereIn('role', ['owner', 'admin'])
                ->where('status', 'active')
                ->exists();

            if ($isClubAdmin) {
                $clubTeamIds = Team::where('club_id', $clubId)->pluck('id');
            } else {
                $childIds = $user->getChildrenIdsInClub($clubId);
                $relevantUserIds = collect([$user->id])->merge($childIds);
                $clubTeamIds = TeamMembership::whereIn('user_id', $relevantUserIds)
                    ->whereHas('team', fn ($q) => $q->where('club_id', $clubId))
                    ->pluck('team_id');
            }

            $teamIds = $teamIds->merge($clubTeamIds);
        }
        $teamIds = $teamIds->unique();

        // All children across all clubs
        $allChildIds = $allChildren->pluck('id');
        $relevantUserIds = collect([$user->id])->merge($allChildIds);

        // Get user's + children's team membership IDs (needed for attendance lookups)
        $teamMembershipIds = TeamMembership::whereIn('user_id', $relevantUserIds)
            ->whereIn('team_id', $teamIds)
            ->pluck('id');

        // Upcoming events (next 7 days) across ALL clubs
        $upcomingEvents = Event::whereIn('team_id', $teamIds)
            ->where('status', 'scheduled')
            ->where('starts_at', '>', now())
            ->where('starts_at', '<=', now()->addDays(7))
            ->with(['team.club', 'venue'])
            ->orderBy('starts_at')
            ->get();

        // Hide events where ALL user's + children's athlete attendances are declined
        $athleteMembershipIds = TeamMembership::whereIn('user_id', $relevantUserIds)
            ->whereIn('team_id', $teamIds)
            ->where('role', 'athlete')
            ->pluck('id');

        if ($athleteMembershipIds->isNotEmpty()) {
            $athleteAttendances = Attendance::whereIn('team_membership_id', $athleteMembershipIds)
                ->whereIn('event_id', $upcomingEvents->pluck('id'))
                ->with('teamMembership')
                ->get();

            $attendanceByEvent = $athleteAttendances->groupBy('event_id');

            $upcomingEvents = $upcomingEvents->filter(function ($event) use ($attendanceByEvent) {
                $attendances = $attendanceByEvent->get($event->id);
                if (!$attendances || $attendances->isEmpty()) {
                    return true;
                }
                return $attendances->contains(fn ($a) => $a->rsvp_status !== 'declined');
            })->values();

            // Build per-event child RSVP status map: eventId => [childName => rsvpStatus]
            $eventChildRsvp = [];
            foreach ($athleteAttendances as $att) {
                $userId = $att->teamMembership->user_id;
                if ($allChildIds->contains($userId)) {
                    $childName = $allChildren->firstWhere('id', $userId)?->first_name;
                    if ($childName) {
                        $eventChildRsvp[$att->event_id][$childName] = $att->rsvp_status;
                    }
                }
            }
        } else {
            $eventChildRsvp = [];
        }

        // Pending RSVP responses for the user + children across ALL clubs (athletes only)
        $pendingAttendances = Attendance::whereIn('team_membership_id', $teamMembershipIds)
            ->where('rsvp_status', 'pending')
            ->whereHas('teamMembership', fn ($q) => $q->where('role', 'athlete'))
            ->whereHas('event', fn ($q) => $q->where('status', 'scheduled')->where('starts_at', '>', now()))
            ->with(['event.team.club', 'event.venue', 'teamMembership.user'])
            ->get()
            ->sortBy('event.starts_at');

        // Group by event for batch modal
        $pendingByEvent = $pendingAttendances->groupBy('event_id');

        // User's teams across ALL clubs with member counts
        $teams = $user->teams()
            ->whereIn('teams.club_id', $allClubIds)
            ->with('club')
            ->withCount('teamMemberships')
            ->get();

        // Children data for dashboard widget + team-to-child mapping (cross-club)
        $childrenData = collect();
        $teamChildMap = [];
        if ($allChildren->isNotEmpty()) {
            foreach ($allChildren as $child) {
                $childHasClub = ClubMembership::where('user_id', $child->id)
                    ->whereIn('club_id', $allClubIds)
                    ->where('status', 'active')
                    ->exists();

                if (!$childHasClub) continue;

                $childTeams = $child->teams()
                    ->whereIn('teams.club_id', $allClubIds)
                    ->with('club')
                    ->get();

                foreach ($childTeams as $ct) {
                    $teamChildMap[$ct->id] = $teamChildMap[$ct->id] ?? [];
                    $teamChildMap[$ct->id][] = $child->first_name;
                }

                $childMembershipIds = TeamMembership::where('user_id', $child->id)
                    ->whereIn('team_id', $childTeams->pluck('id'))
                    ->pluck('id');

                $childPendingCount = Attendance::whereIn('team_membership_id', $childMembershipIds)
                    ->where('rsvp_status', 'pending')
                    ->whereHas('event', fn ($q) => $q->where('status', 'scheduled')->where('starts_at', '>', now()))
                    ->count();

                $childrenData->push((object) [
                    'child' => $child,
                    'teams' => $childTeams,
                    'pendingCount' => $childPendingCount,
                ]);
            }
        }

        // Pending payments for user
        $pendingPayments = MemberPayment::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->with('paymentRequest')
            ->get();

        // Unread notifications count
        $unreadCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // Check if user is coach on any team (across all clubs)
        $isCoach = TeamMembership::where('user_id', $user->id)
            ->whereIn('team_id', $teamIds)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->exists();

        return view('dashboard', [
            'upcomingEvents' => $upcomingEvents,
            'pendingAttendances' => $pendingAttendances,
            'pendingByEvent' => $pendingByEvent,
            'teams' => $teams,
            'pendingPayments' => $pendingPayments,
            'unreadCount' => $unreadCount,
            'isCoach' => $isCoach,
            'childrenData' => $childrenData,
            'teamChildMap' => $teamChildMap,
            'eventChildRsvp' => $eventChildRsvp,
            'multiClub' => $multiClub,
            'clubsMap' => $clubsMap,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\Event;
use App\Models\Team;
use App\Models\TeamMembership;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $calendarStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        // Get ALL clubs where user is an active member
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

        // Build clubs map for display
        $clubsMap = Club::whereIn('id', $allClubIds)->pluck('name', 'id');
        $multiClub = $allClubIds->count() > 1;

        // For each club, determine which teams the user can see
        $allTeamIds = collect();
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
                    ->where('status', 'active')
                    ->whereHas('team', fn ($q) => $q->where('club_id', $clubId))
                    ->pluck('team_id');
            }

            $allTeamIds = $allTeamIds->merge($clubTeamIds);
        }
        $allTeamIds = $allTeamIds->unique();

        // Build children data for filters and team-to-child mapping (across all clubs)
        $children = collect();
        $teamChildMap = []; // teamId => [childName, ...]
        if ($allChildren->isNotEmpty()) {
            $children = $allChildren->filter(fn ($child) =>
                ClubMembership::where('user_id', $child->id)
                    ->whereIn('club_id', $allClubIds)
                    ->where('status', 'active')
                    ->exists()
            )->values();

            $allChildIds = $children->pluck('id');

            foreach ($children as $child) {
                $childTeamIds = TeamMembership::where('user_id', $child->id)
                    ->where('status', 'active')
                    ->whereIn('team_id', $allTeamIds)
                    ->pluck('team_id');

                foreach ($childTeamIds as $tid) {
                    $teamChildMap[$tid] = $teamChildMap[$tid] ?? [];
                    $teamChildMap[$tid][] = $child->first_name;
                }
            }
        } else {
            $allChildIds = collect();
        }

        // Apply child filter from query string
        $selectedChildren = collect($request->input('children', []))
            ->intersect($allChildIds)
            ->values()
            ->all();
        $filteredTeamIds = $allTeamIds;

        if (!empty($selectedChildren) && $allChildIds->isNotEmpty()) {
            $userOwnTeamIds = TeamMembership::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereIn('team_id', $allTeamIds)
                ->pluck('team_id');

            $selectedChildTeamIds = TeamMembership::whereIn('user_id', $selectedChildren)
                ->where('status', 'active')
                ->whereIn('team_id', $allTeamIds)
                ->pluck('team_id');

            $filteredTeamIds = $userOwnTeamIds->merge($selectedChildTeamIds)->unique();
        }

        // Apply club filter from query string
        $selectedClubs = collect($request->input('clubs', []))
            ->intersect($allClubIds)
            ->values()
            ->all();

        // Get events across ALL clubs
        $query = Event::whereIn('team_id', $filteredTeamIds)
            ->where('starts_at', '>=', $calendarStart)
            ->where('starts_at', '<=', $calendarEnd)
            ->where('status', '!=', 'cancelled')
            ->with('team.club', 'venue')
            ->orderBy('starts_at');

        if (!empty($selectedClubs)) {
            $query->whereIn('club_id', $selectedClubs);
        }

        $events = $query->get();

        // Hide events where ALL user's + children's athlete attendances are declined
        $relevantUserIds = collect([$user->id])->merge($allChildIds);
        $athleteMembershipIds = TeamMembership::whereIn('user_id', $relevantUserIds)
            ->whereIn('team_id', $allTeamIds)
            ->where('role', 'athlete')
            ->pluck('id');

        if ($athleteMembershipIds->isNotEmpty() && $events->isNotEmpty()) {
            $declinedByEvent = Attendance::whereIn('team_membership_id', $athleteMembershipIds)
                ->whereIn('event_id', $events->pluck('id'))
                ->get()
                ->groupBy('event_id');

            $events = $events->filter(function ($event) use ($declinedByEvent) {
                $attendances = $declinedByEvent->get($event->id);
                if (!$attendances || $attendances->isEmpty()) {
                    return true;
                }
                return $attendances->contains(fn ($a) => $a->rsvp_status !== 'declined');
            })->values();
        }

        // Group events by date
        $eventsByDate = $events->groupBy(fn ($event) => $event->starts_at->format('Y-m-d'));

        $prevMonth = $startOfMonth->copy()->subMonth();
        $nextMonth = $startOfMonth->copy()->addMonth();

        return view('calendar.index', compact(
            'startOfMonth',
            'endOfMonth',
            'calendarStart',
            'calendarEnd',
            'eventsByDate',
            'prevMonth',
            'nextMonth',
            'month',
            'year',
            'children',
            'teamChildMap',
            'selectedChildren',
            'clubsMap',
            'multiClub',
            'selectedClubs',
            'allClubIds',
        ));
    }
}

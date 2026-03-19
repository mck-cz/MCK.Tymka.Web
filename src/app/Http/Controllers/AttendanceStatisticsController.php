<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClubMembership;
use App\Models\Event;
use App\Models\Team;
use App\Models\TeamMembership;
use Illuminate\Http\Request;

class AttendanceStatisticsController extends Controller
{
    public function index(Request $request)
    {
        $clubId = session('current_club_id');

        $teams = $this->getAccessibleTeams($clubId);

        $selectedTeamId = $request->input('team_id', $teams->first()?->id);
        $selectedTeam = $teams->firstWhere('id', $selectedTeamId);

        $stats = [];

        if ($selectedTeam) {
            // Get past events for this team
            $pastEvents = Event::where('team_id', $selectedTeam->id)
                ->where('club_id', $clubId)
                ->where('starts_at', '<', now())
                ->where('status', '!=', 'cancelled')
                ->orderByDesc('starts_at')
                ->get();

            $totalEvents = $pastEvents->count();

            // Get team members
            $members = TeamMembership::where('team_id', $selectedTeam->id)
                ->where('status', 'active')
                ->with('user')
                ->get();

            // Calculate stats per member
            foreach ($members as $member) {
                $attendances = Attendance::where('team_membership_id', $member->id)
                    ->whereIn('event_id', $pastEvents->pluck('id'))
                    ->get();

                $confirmed = $attendances->where('rsvp_status', 'confirmed')->count();
                $declined = $attendances->where('rsvp_status', 'declined')->count();
                $present = $attendances->where('actual_status', 'present')->count();
                $absent = $attendances->where('actual_status', 'absent')->count();
                $checkedEvents = $attendances->whereNotNull('actual_status')->count();

                $stats[] = [
                    'member' => $member,
                    'total_events' => $totalEvents,
                    'confirmed' => $confirmed,
                    'declined' => $declined,
                    'present' => $present,
                    'absent' => $absent,
                    'checked_events' => $checkedEvents,
                    'rsvp_rate' => $totalEvents > 0 ? round(($confirmed / $totalEvents) * 100) : 0,
                    'attendance_rate' => $checkedEvents > 0 ? round(($present / $checkedEvents) * 100) : 0,
                ];
            }

            // Sort by attendance rate descending
            usort($stats, fn ($a, $b) => $b['attendance_rate'] <=> $a['attendance_rate']);
        }

        return view('statistics.index', compact('teams', 'selectedTeam', 'stats', 'selectedTeamId'));
    }

    public function export(Request $request)
    {
        $clubId = session('current_club_id');
        $teamId = $request->input('team_id');

        $accessibleTeamIds = $this->getAccessibleTeams($clubId)->pluck('id');
        $team = Team::where('club_id', $clubId)->findOrFail($teamId);
        abort_unless($accessibleTeamIds->contains($team->id), 403);

        $pastEvents = Event::where('team_id', $team->id)
            ->where('club_id', $clubId)
            ->where('starts_at', '<', now())
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('starts_at')
            ->get();

        $totalEvents = $pastEvents->count();
        $members = TeamMembership::where('team_id', $team->id)
            ->where('status', 'active')
            ->with('user')
            ->get();

        $csv = __('messages.statistics.export_name') . ','
            . __('messages.statistics.rsvp_confirmed') . ','
            . __('messages.statistics.present') . ','
            . __('messages.statistics.absent') . ','
            . __('messages.statistics.attendance_rate') . "\n";

        foreach ($members as $member) {
            $attendances = Attendance::where('team_membership_id', $member->id)
                ->whereIn('event_id', $pastEvents->pluck('id'))
                ->get();

            $confirmed = $attendances->where('rsvp_status', 'confirmed')->count();
            $present = $attendances->where('actual_status', 'present')->count();
            $absent = $attendances->where('actual_status', 'absent')->count();
            $checked = $attendances->whereNotNull('actual_status')->count();
            $rate = $checked > 0 ? round(($present / $checked) * 100) : 0;

            $csv .= '"' . $member->user->full_name . '",' . $confirmed . ',' . $present . ',' . $absent . ',' . $rate . "%\n";
        }

        $filename = 'attendance-' . $team->name . '-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function getAccessibleTeams(string $clubId)
    {
        $isClubAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', auth()->id())
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        if ($isClubAdmin) {
            return Team::where('club_id', $clubId)->orderBy('name')->get();
        }

        $myTeamIds = TeamMembership::where('user_id', auth()->id())
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->pluck('team_id');

        return Team::where('club_id', $clubId)
            ->whereIn('id', $myTeamIds)
            ->orderBy('name')
            ->get();
    }
}

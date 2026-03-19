<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\TeamMembership;
use Illuminate\Http\Request;

class AttendanceCheckController extends Controller
{
    public function show(Event $event)
    {
        $clubId = session('current_club_id');
        abort_unless($event->club_id === $clubId, 403);

        // Only allow attendance for events that are in_progress, past, or completed
        abort_unless($event->can_record_attendance, 403);

        // Verify user is coach of this team
        $isCoach = TeamMembership::where('user_id', auth()->id())
            ->where('team_id', $event->team_id)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->exists();

        abort_unless($isCoach, 403);

        $attendances = $event->attendances()
            ->with('teamMembership.user')
            ->get()
            ->sortBy([
                fn ($a, $b) => match (true) {
                    $a->rsvp_status === 'confirmed' && $b->rsvp_status !== 'confirmed' => -1,
                    $a->rsvp_status !== 'confirmed' && $b->rsvp_status === 'confirmed' => 1,
                    $a->rsvp_status === 'pending' && $b->rsvp_status === 'declined' => -1,
                    $a->rsvp_status === 'declined' && $b->rsvp_status === 'pending' => 1,
                    default => 0,
                },
            ]);

        return view('events.attendance-check', compact('event', 'attendances'));
    }

    public function update(Request $request, Event $event)
    {
        $clubId = session('current_club_id');
        abort_unless($event->club_id === $clubId, 403);

        // Only allow attendance for events that are in_progress, past, or completed
        abort_unless($event->can_record_attendance, 403);

        $isCoach = TeamMembership::where('user_id', auth()->id())
            ->where('team_id', $event->team_id)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->exists();

        abort_unless($isCoach, 403);

        $request->validate([
            'attendance' => 'required|array',
            'attendance.*' => 'in:present,absent',
        ]);

        $attendanceData = $request->input('attendance', []);

        foreach ($event->attendances as $attendance) {
            if (!array_key_exists($attendance->id, $attendanceData)) {
                continue;
            }
            $attendance->update([
                'actual_status' => $attendanceData[$attendance->id],
                'checked_by' => auth()->id(),
                'checked_at' => now(),
            ]);
        }

        // Auto-complete event after attendance is recorded
        if ($event->status !== 'completed') {
            $event->update([
                'status' => 'completed',
                'completed_by' => auth()->id(),
                'completed_at' => now(),
            ]);
        }

        return redirect()->route('events.show', $event)->with('success', __('messages.attendance_check.saved'));
    }
}

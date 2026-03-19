<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\TeamMembership;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'rsvp_status' => ['required', 'in:confirmed,declined'],
            'rsvp_note' => ['nullable', 'string'],
        ]);

        // Verify event belongs to current club
        abort_unless($attendance->event->club_id === session('current_club_id'), 404);

        // Verify the attendance belongs to the current user or their child
        $this->authorizeAttendance($attendance);

        $attendance->update([
            'rsvp_status' => $validated['rsvp_status'],
            'rsvp_note' => $validated['rsvp_note'] ?? null,
            'responded_by' => Auth::id(),
            'responded_at' => now(),
        ]);

        // Notify team coaches about the RSVP response
        $event = $attendance->event;
        $memberUser = $attendance->teamMembership->user;
        $coachIds = TeamMembership::where('team_id', $event->team_id)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->where('user_id', '!=', Auth::id())
            ->pluck('user_id')
            ->toArray();

        if (!empty($coachIds)) {
            $statusLabel = $validated['rsvp_status'] === 'confirmed'
                ? __('messages.rsvp.confirmed')
                : __('messages.rsvp.declined');

            NotificationService::send(
                $coachIds,
                'rsvp_response',
                __('messages.notifications_msg.rsvp_response', [
                    'name' => $memberUser->first_name . ' ' . $memberUser->last_name,
                    'status' => $statusLabel,
                    'title' => $event->title,
                ])
            );
        }

        return redirect()->back()->with('success', __('messages.events.response_updated'));
    }

    /**
     * Batch update multiple attendance records at once (from dashboard modal).
     */
    public function batchUpdate(Request $request)
    {
        $validated = $request->validate([
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.id' => ['required', 'string'],
            'attendances.*.rsvp_status' => ['required', 'in:confirmed,declined'],
            'attendances.*.rsvp_note' => ['nullable', 'string'],
        ]);

        $updated = 0;

        foreach ($validated['attendances'] as $item) {
            $attendance = Attendance::find($item['id']);
            if (! $attendance) continue;

            $this->authorizeAttendance($attendance);

            $attendance->update([
                'rsvp_status' => $item['rsvp_status'],
                'rsvp_note' => $item['rsvp_note'] ?? null,
                'responded_by' => Auth::id(),
                'responded_at' => now(),
            ]);
            $updated++;
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'updated' => $updated]);
        }

        return redirect()->route('dashboard')
            ->with('success', __('messages.events.batch_response_updated', ['count' => $updated]));
    }

    private function authorizeAttendance(Attendance $attendance): void
    {
        $attendanceUserId = $attendance->teamMembership->user_id;
        $isOwn = $attendanceUserId === Auth::id();
        $isGuardian = !$isOwn && Auth::user()->children()->where('users.id', $attendanceUserId)->exists();
        abort_unless($isOwn || $isGuardian, 403);
    }
}

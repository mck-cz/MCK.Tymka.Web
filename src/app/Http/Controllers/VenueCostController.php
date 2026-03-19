<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\Team;
use App\Models\VenueCost;
use App\Models\VenueCostMemberShare;
use App\Models\VenueCostSettlement;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Penalty;
use App\Models\TeamMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VenueCostController extends Controller
{
    private function authorizeClubAdmin(): void
    {
        $clubId = session('current_club_id');
        $exists = ClubMembership::where('club_id', $clubId)
            ->where('user_id', Auth::id())
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();
        abort_unless($exists, 403);
    }

    public function index()
    {
        $clubId = session('current_club_id');

        $venueCosts = VenueCost::where('club_id', $clubId)
            ->with(['team', 'venueCostSettlements'])
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return view('venue-costs.index', compact('venueCosts'));
    }

    public function create()
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        $teams = Team::where('club_id', $clubId)->orderBy('name')->get();

        return view('venue-costs.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => ['nullable', Rule::exists('teams', 'id')->where('club_id', $clubId)],
            'cost_per_event' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'split_method' => 'required|in:per_attendance,equal_monthly,fixed_per_member',
            'billing_period' => 'required|in:monthly,seasonal,per_event',
            'include_event_types' => 'required|array|min:1',
            'include_event_types.*' => 'in:training,match,competition,tournament',
            'bank_account' => 'nullable|string|max:255',
        ]);

        VenueCost::create([
            ...$validated,
            'include_event_types' => json_encode($validated['include_event_types']),
            'club_id' => $clubId,
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('venue-costs.index')
            ->with('success', __('messages.venue_costs.created'));
    }

    public function edit(VenueCost $venueCost)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($venueCost->club_id === $clubId, 404);

        $teams = Team::where('club_id', $clubId)->orderBy('name')->get();

        return view('venue-costs.edit', compact('venueCost', 'teams'));
    }

    public function update(Request $request, VenueCost $venueCost)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($venueCost->club_id === $clubId, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => ['nullable', Rule::exists('teams', 'id')->where('club_id', $clubId)],
            'cost_per_event' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'split_method' => 'required|in:per_attendance,equal_monthly,fixed_per_member',
            'billing_period' => 'required|in:monthly,seasonal,per_event',
            'include_event_types' => 'required|array|min:1',
            'include_event_types.*' => 'in:training,match,competition,tournament',
            'bank_account' => 'nullable|string|max:255',
        ]);

        $venueCost->update([
            ...$validated,
            'include_event_types' => json_encode($validated['include_event_types']),
        ]);

        return redirect()->route('venue-costs.index')
            ->with('success', __('messages.venue_costs.updated'));
    }

    public function destroy(VenueCost $venueCost)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($venueCost->club_id === $clubId, 404);

        // Delete related settlements and shares
        foreach ($venueCost->venueCostSettlements as $settlement) {
            $settlement->venueCostMemberShares()->delete();
        }
        $venueCost->venueCostSettlements()->delete();
        $venueCost->delete();

        return redirect()->route('venue-costs.index')
            ->with('success', __('messages.venue_costs.deleted'));
    }

    public function generateSettlement(Request $request, VenueCost $venueCost)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($venueCost->club_id === $clubId, 404);

        $validated = $request->validate([
            'period_from' => 'required|date',
            'period_to' => 'required|date|after:period_from',
        ]);

        $periodFrom = $validated['period_from'];
        $periodTo = $validated['period_to'];

        // Get event types to include
        $eventTypes = is_string($venueCost->include_event_types)
            ? json_decode($venueCost->include_event_types, true)
            : ($venueCost->include_event_types ?? ['training']);

        // Find matching events in period
        $eventsQuery = Event::where('club_id', $clubId)
            ->whereIn('event_type', $eventTypes)
            ->where('status', 'scheduled')
            ->whereBetween('starts_at', [$periodFrom, $periodTo]);

        if ($venueCost->team_id) {
            $eventsQuery->where('team_id', $venueCost->team_id);
        }

        $events = $eventsQuery->with('attendances.teamMembership')->get();
        $totalEvents = $events->count();
        $totalCost = $totalEvents * $venueCost->cost_per_event;

        // Count attendances per user
        $attendanceCounts = [];
        foreach ($events as $event) {
            foreach ($event->attendances as $attendance) {
                if ($attendance->actual_status === 'present') {
                    $userId = $attendance->teamMembership->user_id;
                    $attendanceCounts[$userId] = ($attendanceCounts[$userId] ?? 0) + 1;
                }
            }
        }

        // Add penalty attendances (count_as_attendance)
        $eventIds = $events->pluck('id');
        $penalties = Penalty::whereIn('event_id', $eventIds)
            ->where('count_as_attendance', true)
            ->where('waived', false)
            ->get();

        foreach ($penalties as $penalty) {
            $userId = $penalty->user_id;
            $attendanceCounts[$userId] = ($attendanceCounts[$userId] ?? 0) + 1;
        }

        $totalAttendances = array_sum($attendanceCounts);
        $costPerAttendance = $totalAttendances > 0 ? round($totalCost / $totalAttendances, 2) : 0;

        // Create settlement
        $settlement = VenueCostSettlement::create([
            'venue_cost_id' => $venueCost->id,
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'total_events' => $totalEvents,
            'total_cost' => $totalCost,
            'total_attendances' => $totalAttendances,
            'cost_per_attendance' => $costPerAttendance,
            'status' => 'draft',
            'generated_at' => now(),
            'created_by' => Auth::id(),
        ]);

        // Create member shares
        foreach ($attendanceCounts as $userId => $count) {
            $amountDue = round($count * $costPerAttendance, 2);

            VenueCostMemberShare::create([
                'settlement_id' => $settlement->id,
                'user_id' => $userId,
                'attendance_count' => $count,
                'amount_due' => $amountDue,
                'status' => 'pending',
            ]);
        }

        return redirect()->route('venue-cost-settlements.show', $settlement)
            ->with('success', __('messages.venue_costs.settlement_generated'));
    }

    public function showSettlement(VenueCostSettlement $settlement)
    {
        $clubId = session('current_club_id');
        abort_unless($settlement->venueCost->club_id === $clubId, 404);

        $settlement->load(['venueCost', 'venueCostMemberShares.user', 'createdBy']);

        return view('venue-costs.settlement', compact('settlement'));
    }

    public function confirmShare(VenueCostMemberShare $share)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($share->venueCostSettlement->venueCost->club_id === $clubId, 404);

        $share->update([
            'status' => 'paid',
            'paid_at' => now(),
            'confirmed_by' => Auth::id(),
        ]);

        return back()->with('success', __('messages.venue_costs.share_confirmed'));
    }
}

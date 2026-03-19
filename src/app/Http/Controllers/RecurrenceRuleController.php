<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\RecurrenceRule;
use App\Models\Team;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RecurrenceRuleController extends Controller
{
    private function authorizeClubAdmin(): void
    {
        $clubId = session('current_club_id');
        $membership = ClubMembership::where('club_id', $clubId)
            ->where('user_id', Auth::id())
            ->first();
        abort_unless($membership && in_array($membership->role, ['owner', 'admin']), 403);
    }

    public function index()
    {
        $clubId = session('current_club_id');

        $rules = RecurrenceRule::where('club_id', $clubId)
            ->with(['team', 'venue', 'createdBy'])
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('recurrence-rules.index', compact('rules'));
    }

    public function create()
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        $teams = Team::where('club_id', $clubId)->orderBy('name')->get();
        $venues = Venue::where('club_id', $clubId)->orderBy('name')->get();

        return view('recurrence-rules.create', compact('teams', 'venues'));
    }

    public function store(Request $request)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'event_type' => 'required|in:training,match,competition,tournament',
            'team_id' => ['required', Rule::exists('teams', 'id')->where('club_id', $clubId)],
            'venue_id' => ['nullable', Rule::exists('venues', 'id')->where('club_id', $clubId)],
            'frequency' => 'required|in:weekly,biweekly,monthly',
            'day_of_week' => 'required|integer|min:0|max:6',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'auto_create_days_ahead' => 'required|integer|min:1|max:90',
        ]);

        RecurrenceRule::create([
            ...$validated,
            'club_id' => $clubId,
            'interval' => $validated['frequency'] === 'biweekly' ? 2 : 1,
            'created_by' => Auth::id(),
            'is_active' => true,
            'auto_rsvp' => true,
        ]);

        return redirect()->route('recurrence-rules.index')
            ->with('success', __('messages.recurrence.created'));
    }

    public function edit(RecurrenceRule $recurrenceRule)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($recurrenceRule->club_id === $clubId, 404);

        $teams = Team::where('club_id', $clubId)->orderBy('name')->get();
        $venues = Venue::where('club_id', $clubId)->orderBy('name')->get();

        return view('recurrence-rules.edit', compact('recurrenceRule', 'teams', 'venues'));
    }

    public function update(Request $request, RecurrenceRule $recurrenceRule)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($recurrenceRule->club_id === $clubId, 404);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'event_type' => 'required|in:training,match,competition,tournament',
            'team_id' => ['required', Rule::exists('teams', 'id')->where('club_id', $clubId)],
            'venue_id' => ['nullable', Rule::exists('venues', 'id')->where('club_id', $clubId)],
            'frequency' => 'required|in:weekly,biweekly,monthly',
            'day_of_week' => 'required|integer|min:0|max:6',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'auto_create_days_ahead' => 'required|integer|min:1|max:90',
        ]);

        $recurrenceRule->update([
            ...$validated,
            'interval' => $validated['frequency'] === 'biweekly' ? 2 : 1,
        ]);

        return redirect()->route('recurrence-rules.index')
            ->with('success', __('messages.recurrence.updated'));
    }

    public function toggleActive(RecurrenceRule $recurrenceRule)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($recurrenceRule->club_id === $clubId, 404);

        $recurrenceRule->update(['is_active' => !$recurrenceRule->is_active]);

        return back()->with('success', __('messages.recurrence.toggled'));
    }

    public function destroy(RecurrenceRule $recurrenceRule)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($recurrenceRule->club_id === $clubId, 404);

        $recurrenceRule->recurrenceExclusions()->delete();
        $recurrenceRule->delete();

        return redirect()->route('recurrence-rules.index')
            ->with('success', __('messages.recurrence.deleted'));
    }
}

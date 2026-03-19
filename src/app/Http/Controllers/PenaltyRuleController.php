<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\PenaltyRule;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PenaltyRuleController extends Controller
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

        $rules = PenaltyRule::where('club_id', $clubId)
            ->with('team')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return view('penalty-rules.index', compact('rules'));
    }

    public function create()
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        $teams = Team::where('club_id', $clubId)->orderBy('name')->get();

        return view('penalty-rules.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => ['nullable', Rule::exists('teams', 'id')->where('club_id', $clubId)],
            'trigger_type' => 'required|in:no_show,late_cancel,no_response',
            'penalty_type' => 'required|in:count_as_attended,fixed_amount,percentage_surcharge',
            'amount' => 'nullable|numeric|min:0',
            'late_cancel_hours' => 'nullable|integer|min:1|max:72',
            'grace_count' => 'integer|min:0|max:10',
        ]);

        PenaltyRule::create([
            ...$validated,
            'club_id' => $clubId,
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('penalty-rules.index')
            ->with('success', __('messages.penalties.rule_created'));
    }

    public function edit(PenaltyRule $penaltyRule)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($penaltyRule->club_id === $clubId, 404);

        $teams = Team::where('club_id', $clubId)->orderBy('name')->get();

        return view('penalty-rules.edit', compact('penaltyRule', 'teams'));
    }

    public function update(Request $request, PenaltyRule $penaltyRule)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($penaltyRule->club_id === $clubId, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => ['nullable', Rule::exists('teams', 'id')->where('club_id', $clubId)],
            'trigger_type' => 'required|in:no_show,late_cancel,no_response',
            'penalty_type' => 'required|in:count_as_attended,fixed_amount,percentage_surcharge',
            'amount' => 'nullable|numeric|min:0',
            'late_cancel_hours' => 'nullable|integer|min:1|max:72',
            'grace_count' => 'integer|min:0|max:10',
        ]);

        $penaltyRule->update($validated);

        return redirect()->route('penalty-rules.index')
            ->with('success', __('messages.penalties.rule_updated'));
    }

    public function toggleActive(PenaltyRule $penaltyRule)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($penaltyRule->club_id === $clubId, 404);

        $penaltyRule->update(['is_active' => !$penaltyRule->is_active]);

        return back()->with('success', __('messages.penalties.rule_toggled'));
    }

    public function destroy(PenaltyRule $penaltyRule)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($penaltyRule->club_id === $clubId, 404);

        $penaltyRule->penalties()->delete();
        $penaltyRule->delete();

        return redirect()->route('penalty-rules.index')
            ->with('success', __('messages.penalties.rule_deleted'));
    }
}

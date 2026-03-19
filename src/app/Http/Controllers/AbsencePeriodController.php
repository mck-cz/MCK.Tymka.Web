<?php

namespace App\Http\Controllers;

use App\Models\AbsencePeriod;
use App\Models\Team;
use App\Models\TeamMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsencePeriodController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $absences = AbsencePeriod::where('created_by', $userId)
            ->orderBy('starts_at', 'desc')
            ->get();

        return view('absences.index', compact('absences'));
    }

    public function create()
    {
        $clubId = session('current_club_id');
        $userId = Auth::id();

        $teams = Team::where('club_id', $clubId)
            ->whereHas('teamMemberships', fn ($q) => $q->where('user_id', $userId)->where('status', 'active'))
            ->orderBy('name')
            ->get();

        return view('absences.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason_type' => 'required|in:vacation,illness,injury,personal,other',
            'reason_note' => 'nullable|string|max:500',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
            'apply_to_teams' => 'nullable|array',
            'apply_to_teams.*' => 'uuid',
        ]);

        AbsencePeriod::create([
            'created_by' => Auth::id(),
            'reason_type' => $validated['reason_type'],
            'reason_note' => $validated['reason_note'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'apply_to_teams' => $validated['apply_to_teams'] ?? null,
        ]);

        return redirect()->route('absences.index')
            ->with('success', __('messages.absences.created'));
    }

    public function destroy(AbsencePeriod $absencePeriod)
    {
        abort_unless($absencePeriod->created_by === Auth::id(), 403);

        $absencePeriod->delete();

        return redirect()->route('absences.index')
            ->with('success', __('messages.absences.deleted'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\Season;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function index()
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');

        $seasons = Season::where('club_id', $clubId)
            ->withCount('teams')
            ->orderByDesc('start_date')
            ->get();

        return view('seasons.index', compact('seasons'));
    }

    public function create()
    {
        $this->authorizeClubAdmin();

        return view('seasons.create');
    }

    public function store(Request $request)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $validated['club_id'] = $clubId;

        Season::create($validated);

        return redirect()->route('seasons.index')->with('success', __('messages.seasons.created'));
    }

    public function edit(Season $season)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($season->club_id === $clubId, 403);

        return view('seasons.edit', compact('season'));
    }

    public function update(Request $request, Season $season)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($season->club_id === $clubId, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $season->update($validated);

        return redirect()->route('seasons.index')->with('success', __('messages.seasons.updated'));
    }

    public function destroy(Season $season)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($season->club_id === $clubId, 403);

        if ($season->teams()->exists()) {
            return back()->withErrors(['season' => __('messages.seasons.has_teams')]);
        }

        $season->delete();

        return redirect()->route('seasons.index')->with('success', __('messages.seasons.deleted'));
    }

    private function authorizeClubAdmin(): void
    {
        $clubId = session('current_club_id');
        $membership = ClubMembership::where('club_id', $clubId)
            ->where('user_id', auth()->id())
            ->first();

        abort_unless($membership && in_array($membership->role, ['owner', 'admin']), 403);
    }
}

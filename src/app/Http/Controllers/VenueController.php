<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\TeamMembership;
use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index()
    {
        $clubId = session('current_club_id');

        $venues = Venue::where('club_id', $clubId)
            ->orderByDesc('is_favorite')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('venues.index', compact('venues'));
    }

    public function create()
    {
        $this->authorizeCoachOrAdmin();

        return view('venues.create');
    }

    public function store(Request $request)
    {
        $this->authorizeCoachOrAdmin();

        $clubId = session('current_club_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'sport_type' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'is_favorite' => 'sometimes|boolean',
        ]);

        $validated['club_id'] = $clubId;
        $validated['is_favorite'] = $request->boolean('is_favorite');
        $validated['geocoding_source'] = ($validated['latitude'] !== null && $validated['longitude'] !== null) ? 'manual' : null;

        Venue::create($validated);

        return redirect()->route('venues.index')->with('success', __('messages.venues.created'));
    }

    public function edit(Venue $venue)
    {
        $this->authorizeCoachOrAdmin();

        $clubId = session('current_club_id');
        abort_unless($venue->club_id === $clubId, 403);

        return view('venues.edit', compact('venue'));
    }

    public function update(Request $request, Venue $venue)
    {
        $this->authorizeCoachOrAdmin();

        $clubId = session('current_club_id');
        abort_unless($venue->club_id === $clubId, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'sport_type' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'is_favorite' => 'sometimes|boolean',
        ]);

        $validated['is_favorite'] = $request->boolean('is_favorite');
        $validated['geocoding_source'] = ($validated['latitude'] !== null && $validated['longitude'] !== null) ? 'manual' : null;

        $venue->update($validated);

        return redirect()->route('venues.index')->with('success', __('messages.venues.updated'));
    }

    public function destroy(Venue $venue)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($venue->club_id === $clubId, 403);

        $venue->delete();

        return redirect()->route('venues.index')->with('success', __('messages.venues.deleted'));
    }

    private function authorizeCoachOrAdmin(): void
    {
        $clubId = session('current_club_id');
        $userId = auth()->id();

        $isAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        if ($isAdmin) {
            return;
        }

        $isCoach = TeamMembership::where('user_id', $userId)
            ->whereHas('team', fn($q) => $q->where('club_id', $clubId))
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->exists();

        abort_unless($isCoach, 403);
    }

    private function authorizeClubAdmin(): void
    {
        $clubId = session('current_club_id');
        $exists = ClubMembership::where('club_id', $clubId)
            ->where('user_id', auth()->id())
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        abort_unless($exists, 403);
    }
}

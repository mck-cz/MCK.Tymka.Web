<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\JoinRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->clubMemberships()->exists()) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.index');
    }

    public function createClub()
    {
        return view('onboarding.create-club');
    }

    public function storeClub(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'primary_sport' => 'required',
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        while (Club::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $club = Club::create([
            'name' => $request->name,
            'slug' => $slug,
            'primary_sport' => $request->primary_sport,
            'billing_plan' => 'free',
            'color' => '#1B6B4A',
        ]);

        ClubMembership::create([
            'user_id' => auth()->id(),
            'club_id' => $club->id,
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        session(['current_club_id' => $club->id]);

        return redirect()->route('dashboard')->with('success', __('messages.onboarding.club_created'));
    }

    public function joinClub()
    {
        return view('onboarding.join-club');
    }

    public function searchClubs(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $escaped = str_replace(['%', '_'], ['\%', '\_'], $query);
        $clubs = Club::where('name', 'LIKE', "%{$escaped}%")
            ->select('id', 'name', 'primary_sport', 'slug')
            ->limit(10)
            ->get();

        return response()->json($clubs);
    }

    public function requestJoin(Request $request)
    {
        $request->validate([
            'club_id' => 'required|exists:clubs,id',
            'message' => 'nullable|max:500',
        ]);

        $user = auth()->user();

        if (ClubMembership::where('user_id', $user->id)->where('club_id', $request->club_id)->exists()) {
            return redirect()->route('onboarding')->with('error', __('messages.onboarding.already_member'));
        }

        JoinRequest::create([
            'club_id' => $request->club_id,
            'user_id' => $user->id,
            'status' => 'pending',
            'message' => $request->message,
        ]);

        return redirect()->route('onboarding')->with('success', __('messages.onboarding.request_sent'));
    }
}

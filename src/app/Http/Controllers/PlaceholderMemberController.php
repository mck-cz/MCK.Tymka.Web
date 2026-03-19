<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClubMembership;
use App\Models\Event;
use App\Models\MemberClaimRequest;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\User;
use App\Models\UserGuardian;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlaceholderMemberController extends Controller
{
    /**
     * Create a placeholder member (child without account) and optionally send guardian invite.
     */
    public function store(Request $request, Team $team)
    {
        $clubId = session('current_club_id');
        abort_unless($team->club_id === $clubId, 404);
        $this->authorizeTeamEdit($team);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'role' => 'required|in:head_coach,assistant_coach,athlete',
            'sex' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date|before:today',
            'guardian_email' => 'nullable|email|max:255',
            'is_guardian_invite' => 'sometimes|boolean',
        ]);

        // Create placeholder user
        $placeholder = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => 'placeholder_' . Str::uuid() . '@tymko.placeholder',
            'password' => bcrypt(Str::random(64)),
            'status' => 'placeholder',
            'sex' => $validated['sex'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'created_by_role' => 'coach',
        ]);

        // Create team membership
        $membership = TeamMembership::create([
            'team_id' => $team->id,
            'user_id' => $placeholder->id,
            'role' => $validated['role'],
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Auto-create attendance records for future scheduled events of this team
        $futureEvents = Event::where('team_id', $team->id)
            ->where('starts_at', '>', now())
            ->where('status', 'scheduled')
            ->get();

        foreach ($futureEvents as $event) {
            Attendance::firstOrCreate(
                ['event_id' => $event->id, 'team_membership_id' => $membership->id],
                ['rsvp_status' => 'pending'],
            );
        }

        // If guardian invite requested, create claim request
        if ($request->boolean('is_guardian_invite') && !empty($validated['guardian_email'])) {
            MemberClaimRequest::create([
                'placeholder_id' => $placeholder->id,
                'club_id' => $clubId,
                'team_id' => $team->id,
                'requested_by' => auth()->id(),
                'target_email' => $validated['guardian_email'],
                'token' => Str::random(64),
                'link_type' => 'guardian_invite',
                'status' => 'pending',
                'expires_at' => now()->addDays(30),
            ]);
        }

        return back()->with('success', __('messages.placeholder.member_created'));
    }

    /**
     * Send a guardian invite for an existing placeholder member.
     */
    public function sendGuardianInvite(Request $request, Team $team, User $placeholder)
    {
        $clubId = session('current_club_id');
        abort_unless($team->club_id === $clubId, 404);
        $this->authorizeTeamEdit($team);
        abort_unless($placeholder->status === 'placeholder', 422);

        $request->validate([
            'guardian_email' => 'required|email|max:255',
        ]);

        MemberClaimRequest::create([
            'placeholder_id' => $placeholder->id,
            'club_id' => $clubId,
            'team_id' => $team->id,
            'requested_by' => auth()->id(),
            'target_email' => $request->input('guardian_email'),
            'token' => Str::random(64),
            'link_type' => 'guardian_invite',
            'status' => 'pending',
            'expires_at' => now()->addDays(30),
        ]);

        return back()->with('success', __('messages.placeholder.invite_sent'));
    }

    /**
     * Show the claim page for a guardian invite token.
     */
    public function showClaim(string $token)
    {
        $claim = MemberClaimRequest::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->with(['placeholder', 'club', 'team'])
            ->firstOrFail();

        $placeholder = $claim->placeholder;

        return view('placeholder.claim', compact('claim', 'placeholder'));
    }

    /**
     * Process the claim - logged-in parent accepts guardian role.
     */
    public function processClaim(Request $request, string $token)
    {
        $claim = MemberClaimRequest::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->with('placeholder')
            ->firstOrFail();

        $guardian = auth()->user();
        $child = $claim->placeholder;

        // Create guardian-child relationship
        UserGuardian::firstOrCreate(
            ['guardian_id' => $guardian->id, 'child_id' => $child->id],
            ['relationship' => 'parent', 'is_primary' => true]
        );

        // Update placeholder - mark as claimed
        $child->update([
            'claimed_by' => $guardian->id,
            'claimed_at' => now(),
        ]);

        // Ensure guardian is member of the club
        ClubMembership::firstOrCreate(
            ['user_id' => $guardian->id, 'club_id' => $claim->club_id],
            ['role' => 'member', 'status' => 'active', 'joined_at' => now()]
        );

        // Update claim status
        $claim->update([
            'status' => 'accepted',
            'matched_user_id' => $guardian->id,
            'accepted_by' => $guardian->id,
            'accepted_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', __('messages.placeholder.claim_accepted', ['name' => $child->full_name]));
    }

    private function authorizeTeamEdit(Team $team): void
    {
        $clubId = session('current_club_id');
        $userId = auth()->id();

        $isClubAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        if ($isClubAdmin) {
            return;
        }

        $isCoach = TeamMembership::where('team_id', $team->id)
            ->where('user_id', $userId)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->exists();

        abort_unless($isCoach, 403);
    }
}

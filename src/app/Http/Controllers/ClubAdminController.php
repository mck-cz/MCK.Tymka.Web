<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\JoinRequest;
use Illuminate\Http\Request;

class ClubAdminController extends Controller
{
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

    public function index()
    {
        $clubId = session('current_club_id');
        $club = Club::findOrFail($clubId);
        $this->authorizeClubAdmin();

        $membership = ClubMembership::where('club_id', $clubId)
            ->where('user_id', auth()->id())
            ->first();

        $members = ClubMembership::where('club_id', $clubId)
            ->with('user')
            ->orderByRaw("CASE role WHEN 'owner' THEN 1 WHEN 'admin' THEN 2 WHEN 'member' THEN 3 ELSE 4 END")
            ->get();

        $pendingRequests = JoinRequest::where('club_id', $clubId)
            ->where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        return view('club-admin.index', compact('club', 'members', 'pendingRequests', 'membership'));
    }

    public function editSettings()
    {
        $clubId = session('current_club_id');
        $club = Club::findOrFail($clubId);
        $this->authorizeClubAdmin();

        return view('club-admin.settings', compact('club'));
    }

    public function updateSettings(Request $request)
    {
        $clubId = session('current_club_id');
        $club = Club::findOrFail($clubId);
        $this->authorizeClubAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'primary_sport' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'bank_account' => 'nullable|string|max:50',
            'event_in_progress_minutes' => 'nullable|integer|min:0|max:1440',
        ]);

        // Extract settings fields and merge into club settings JSON
        $minutes = $validated['event_in_progress_minutes'] ?? 60;
        unset($validated['event_in_progress_minutes']);

        $settings = $club->settings ?? [];
        $settings['event_in_progress_minutes'] = (int) $minutes;
        $validated['settings'] = $settings;

        $club->update($validated);

        return redirect()->route('club-admin.settings')
            ->with('success', __('messages.club_admin.settings_updated'));
    }

    public function updateRole(Request $request, ClubMembership $clubMembership)
    {
        $clubId = session('current_club_id');
        abort_unless($clubMembership->club_id === $clubId, 403);

        $currentMembership = ClubMembership::where('club_id', $clubId)
            ->where('user_id', auth()->id())
            ->first();

        abort_unless($currentMembership && $currentMembership->role === 'owner', 403);

        // Cannot change own role
        abort_if($clubMembership->user_id === auth()->id(), 403);

        // Cannot make someone owner (owner transfer is separate)
        $request->validate([
            'role' => 'required|in:admin,member',
        ]);

        $clubMembership->update(['role' => $request->input('role')]);

        return back()->with('success', __('messages.club_admin.role_updated'));
    }

    public function removeMember(ClubMembership $clubMembership)
    {
        $clubId = session('current_club_id');
        abort_unless($clubMembership->club_id === $clubId, 403);

        $currentMembership = ClubMembership::where('club_id', $clubId)
            ->where('user_id', auth()->id())
            ->first();

        abort_unless($currentMembership && in_array($currentMembership->role, ['owner', 'admin']), 403);

        // Cannot remove owner
        abort_if($clubMembership->role === 'owner', 403);

        // Cannot remove yourself
        abort_if($clubMembership->user_id === auth()->id(), 403);

        $clubMembership->delete();

        return back()->with('success', __('messages.club_admin.member_removed'));
    }

    public function approveRequest(JoinRequest $joinRequest)
    {
        $clubId = session('current_club_id');
        abort_unless($joinRequest->club_id === $clubId, 403);
        abort_unless($joinRequest->status === 'pending', 404);

        $currentMembership = ClubMembership::where('club_id', $clubId)
            ->where('user_id', auth()->id())
            ->first();

        abort_unless($currentMembership && in_array($currentMembership->role, ['owner', 'admin']), 403);

        // Check if already member
        $existing = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $joinRequest->user_id)
            ->exists();

        if (!$existing) {
            ClubMembership::create([
                'user_id' => $joinRequest->user_id,
                'club_id' => $clubId,
                'role' => 'member',
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        $joinRequest->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
        ]);

        return back()->with('success', __('messages.club_admin.request_approved'));
    }

    public function rejectRequest(JoinRequest $joinRequest)
    {
        $clubId = session('current_club_id');
        abort_unless($joinRequest->club_id === $clubId, 403);
        abort_unless($joinRequest->status === 'pending', 404);

        $currentMembership = ClubMembership::where('club_id', $clubId)
            ->where('user_id', auth()->id())
            ->first();

        abort_unless($currentMembership && in_array($currentMembership->role, ['owner', 'admin']), 403);

        $joinRequest->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
        ]);

        return back()->with('success', __('messages.club_admin.request_rejected'));
    }
}

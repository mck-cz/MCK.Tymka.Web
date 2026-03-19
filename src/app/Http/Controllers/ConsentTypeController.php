<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\Consent;
use App\Models\ConsentType;
use App\Models\User;
use App\Models\UserGuardian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsentTypeController extends Controller
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

        $consentTypes = ConsentType::where('club_id', $clubId)
            ->orderBy('sort_order')
            ->get();

        // User's own consents
        $userConsents = Consent::where('user_id', Auth::id())
            ->whereNull('child_id')
            ->whereHas('consentType', fn ($q) => $q->where('club_id', $clubId))
            ->get()
            ->keyBy('consent_type_id');

        // Children the user is guardian of (who are members of this club)
        $children = Auth::user()->children()
            ->whereHas('clubMemberships', fn ($q) => $q->where('club_id', $clubId))
            ->get();

        // Children's consents
        $childConsents = [];
        if ($children->isNotEmpty()) {
            $childConsents = Consent::whereIn('child_id', $children->pluck('id'))
                ->whereHas('consentType', fn ($q) => $q->where('club_id', $clubId))
                ->get()
                ->groupBy('child_id')
                ->map(fn ($consents) => $consents->keyBy('consent_type_id'));
        }

        return view('consents.index', compact('consentTypes', 'userConsents', 'children', 'childConsents'));
    }

    public function create()
    {
        $this->authorizeClubAdmin();

        return view('consents.create');
    }

    public function edit(ConsentType $consentType)
    {
        $this->authorizeClubAdmin();
        $clubId = session('current_club_id');
        abort_unless($consentType->club_id === $clubId, 404);

        return view('consents.edit', compact('consentType'));
    }

    public function store(Request $request)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'content' => 'nullable|string|max:65000',
            'is_required' => 'boolean',
        ]);

        $maxOrder = ConsentType::where('club_id', $clubId)->max('sort_order') ?? 0;

        ConsentType::create([
            ...$validated,
            'club_id' => $clubId,
            'is_required' => $validated['is_required'] ?? false,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('consents.index')->with('success', __('messages.consents.type_created'));
    }

    public function update(Request $request, ConsentType $consentType)
    {
        $this->authorizeClubAdmin();
        $clubId = session('current_club_id');
        abort_unless($consentType->club_id === $clubId, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'content' => 'nullable|string|max:65000',
            'is_required' => 'boolean',
        ]);

        $consentType->update([
            ...$validated,
            'is_required' => $validated['is_required'] ?? false,
        ]);

        return redirect()->route('consents.index')->with('success', __('messages.consents.type_updated'));
    }

    public function destroy(ConsentType $consentType)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($consentType->club_id === $clubId, 404);

        $consentType->consents()->delete();
        $consentType->delete();

        return back()->with('success', __('messages.consents.type_deleted'));
    }

    public function overview(Request $request)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        $search = $request->get('search', '');
        $typeFilter = $request->get('type', '');
        $statusFilter = $request->get('status', '');

        $consentTypes = ConsentType::where('club_id', $clubId)
            ->orderBy('sort_order')
            ->get();

        $query = Consent::whereHas('consentType', fn ($q) => $q->where('club_id', $clubId))
            ->with(['consentType', 'user', 'child', 'grantedBy']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($uq) => $uq->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"))
                ->orWhereHas('child', fn ($cq) => $cq->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        if ($typeFilter) {
            $query->where('consent_type_id', $typeFilter);
        }

        if ($statusFilter === 'granted') {
            $query->where('granted', true);
        } elseif ($statusFilter === 'revoked') {
            $query->where('granted', false);
        }

        $consents = $query->orderByDesc('granted_at')->paginate(25)->withQueryString();

        return view('consents.overview', compact('consents', 'consentTypes', 'search', 'typeFilter', 'statusFilter'));
    }

    public function grant(Request $request, ConsentType $consentType)
    {
        $clubId = session('current_club_id');
        abort_unless($consentType->club_id === $clubId, 404);

        $userId = Auth::id();
        $childId = $request->input('child_id');

        // If granting for a child, verify guardian relationship
        if ($childId) {
            $isGuardian = UserGuardian::where('guardian_id', $userId)
                ->where('child_id', $childId)
                ->exists();
            abort_unless($isGuardian, 403);

            Consent::updateOrCreate(
                ['consent_type_id' => $consentType->id, 'user_id' => $userId, 'child_id' => $childId],
                [
                    'granted' => true,
                    'granted_by' => $userId,
                    'granted_at' => now(),
                    'revoked_at' => null,
                ]
            );
        } else {
            Consent::updateOrCreate(
                ['consent_type_id' => $consentType->id, 'user_id' => $userId, 'child_id' => null],
                [
                    'granted' => true,
                    'granted_by' => $userId,
                    'granted_at' => now(),
                    'revoked_at' => null,
                ]
            );
        }

        return back()->with('success', __('messages.consents.granted'));
    }

    public function revoke(Request $request, ConsentType $consentType)
    {
        $clubId = session('current_club_id');
        abort_unless($consentType->club_id === $clubId, 404);

        $childId = $request->input('child_id');

        $query = Consent::where('consent_type_id', $consentType->id)
            ->where('user_id', Auth::id());

        if ($childId) {
            $query->where('child_id', $childId);
        } else {
            $query->whereNull('child_id');
        }

        $consent = $query->first();

        if ($consent) {
            $consent->update([
                'granted' => false,
                'revoked_at' => now(),
            ]);
        }

        return back()->with('success', __('messages.consents.revoked'));
    }

    public function show(ConsentType $consentType)
    {
        $clubId = session('current_club_id');
        abort_unless($consentType->club_id === $clubId, 404);

        return view('consents.show', compact('consentType'));
    }
}

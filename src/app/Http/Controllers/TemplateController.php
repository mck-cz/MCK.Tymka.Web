<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\EquipmentTemplate;
use App\Models\InstructionTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
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

        $equipmentTemplates = EquipmentTemplate::where('club_id', $clubId)
            ->orderBy('event_type')
            ->orderBy('sort_order')
            ->get();

        $instructionTemplates = InstructionTemplate::where('club_id', $clubId)
            ->orderBy('event_type')
            ->orderBy('sort_order')
            ->get();

        return view('templates.index', compact('equipmentTemplates', 'instructionTemplates'));
    }

    public function storeEquipment(Request $request)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'event_type' => 'required|in:training,match,competition,tournament',
        ]);

        EquipmentTemplate::create([
            ...$validated,
            'club_id' => $clubId,
            'sort_order' => 0,
        ]);

        return back()->with('success', __('messages.templates.equipment_created'));
    }

    public function storeInstruction(Request $request)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'body' => 'required|string|max:2000',
            'event_type' => 'required|in:training,match,competition,tournament',
        ]);

        InstructionTemplate::create([
            ...$validated,
            'club_id' => $clubId,
            'sort_order' => 0,
        ]);

        return back()->with('success', __('messages.templates.instruction_created'));
    }

    public function destroyEquipment(EquipmentTemplate $equipmentTemplate)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($equipmentTemplate->club_id === $clubId, 404);

        $equipmentTemplate->delete();

        return back()->with('success', __('messages.templates.deleted'));
    }

    public function destroyInstruction(InstructionTemplate $instructionTemplate)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($instructionTemplate->club_id === $clubId, 404);

        $instructionTemplate->delete();

        return back()->with('success', __('messages.templates.deleted'));
    }
}

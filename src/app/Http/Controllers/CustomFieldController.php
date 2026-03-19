<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    public function index()
    {
        $clubId = session('current_club_id');

        $fields = CustomFieldDefinition::where('club_id', $clubId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('custom-fields.index', compact('fields'));
    }

    public function create()
    {
        $this->authorizeClubAdmin();

        return view('custom-fields.create');
    }

    public function store(Request $request)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'entity_type' => 'required|in:member',
            'field_type' => 'required|in:text,textarea,number_int,number_decimal,checkbox,select,multi_select,date',
            'options' => 'nullable|string',
            'default_value' => 'nullable|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string|max:500',
            'suffix' => 'nullable|string|max:20',
            'is_required' => 'sometimes|boolean',
            'validation_min' => 'nullable|numeric',
            'validation_max' => 'nullable|numeric',
            'visibility_read' => 'required|in:everyone,coaches,admins',
            'visibility_write' => 'required|in:member,coaches,admins',
            'show_in_registration' => 'sometimes|boolean',
            'show_in_roster' => 'sometimes|boolean',
        ]);

        $validated['club_id'] = $clubId;
        $validated['created_by'] = auth()->id();
        $validated['is_required'] = $request->boolean('is_required');
        $validated['show_in_registration'] = $request->boolean('show_in_registration');
        $validated['show_in_roster'] = $request->boolean('show_in_roster');

        // Parse options for select/multi_select
        if (in_array($validated['field_type'], ['select', 'multi_select']) && !empty($validated['options'])) {
            $validated['options'] = array_map('trim', explode("\n", $validated['options']));
        } else {
            $validated['options'] = null;
        }

        $validated['sort_order'] = CustomFieldDefinition::where('club_id', $clubId)->max('sort_order') + 1;

        CustomFieldDefinition::create($validated);

        return redirect()->route('custom-fields.index')->with('success', __('messages.custom_fields.created'));
    }

    public function edit(CustomFieldDefinition $customField)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($customField->club_id === $clubId, 403);

        return view('custom-fields.edit', compact('customField'));
    }

    public function update(Request $request, CustomFieldDefinition $customField)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($customField->club_id === $clubId, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'field_type' => 'required|in:text,textarea,number_int,number_decimal,checkbox,select,multi_select,date',
            'options' => 'nullable|string',
            'default_value' => 'nullable|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string|max:500',
            'suffix' => 'nullable|string|max:20',
            'is_required' => 'sometimes|boolean',
            'validation_min' => 'nullable|numeric',
            'validation_max' => 'nullable|numeric',
            'visibility_read' => 'required|in:everyone,coaches,admins',
            'visibility_write' => 'required|in:member,coaches,admins',
            'show_in_registration' => 'sometimes|boolean',
            'show_in_roster' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_required'] = $request->boolean('is_required');
        $validated['show_in_registration'] = $request->boolean('show_in_registration');
        $validated['show_in_roster'] = $request->boolean('show_in_roster');
        $validated['is_active'] = $request->boolean('is_active');

        if (in_array($validated['field_type'], ['select', 'multi_select']) && !empty($validated['options'])) {
            $validated['options'] = array_map('trim', explode("\n", $validated['options']));
        } else {
            $validated['options'] = null;
        }

        $customField->update($validated);

        return redirect()->route('custom-fields.index')->with('success', __('messages.custom_fields.updated'));
    }

    public function destroy(CustomFieldDefinition $customField)
    {
        $this->authorizeClubAdmin();

        $clubId = session('current_club_id');
        abort_unless($customField->club_id === $clubId, 403);

        $customField->delete();

        return redirect()->route('custom-fields.index')->with('success', __('messages.custom_fields.deleted'));
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

<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use Illuminate\Http\Request;

class ClubSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate([
            'club_id' => 'required|uuid',
        ]);

        $membership = ClubMembership::where('club_id', $request->input('club_id'))
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->firstOrFail();

        session(['current_club_id' => $membership->club_id]);

        return redirect()->route('dashboard');
    }
}

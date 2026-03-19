<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Show the settings page.
     */
    public function index()
    {
        return view('settings.index', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the user's locale preference.
     */
    public function updateLocale(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'in:cs,en'],
        ]);

        $user = Auth::user();
        $user->update(['locale' => $validated['locale']]);

        session(['locale' => $validated['locale']]);

        return redirect()->back()->with('success', __('messages.settings.saved'));
    }
}

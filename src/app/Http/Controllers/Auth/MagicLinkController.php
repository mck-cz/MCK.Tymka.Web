<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MagicLinkController extends Controller
{
    public function showForm()
    {
        return view('auth.magic-link');
    }

    public function sendLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Str::random(64);

            Cache::put("magic_link:{$token}", $user->id, now()->addMinutes(15));

            $url = route('magic-link.verify', ['token' => $token]);

            // TODO: Send email with $url to $user->email
            // Mail::to($user)->send(new MagicLinkMail($url));
        }

        // Always show success to prevent email enumeration
        return back()->with('status', __('messages.auth.magic_link_sent'));
    }

    public function verify(Request $request, string $token)
    {
        $userId = Cache::pull("magic_link:{$token}");

        if (! $userId) {
            return redirect()->route('login')->withErrors([
                'email' => __('messages.auth.magic_link_expired'),
            ]);
        }

        $user = User::findOrFail($userId);

        Auth::login($user, remember: true);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}

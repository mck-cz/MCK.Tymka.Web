<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClubSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Check if user has any club memberships
        $memberships = $user->clubMemberships ?? collect();

        if ($memberships->isEmpty()) {
            return redirect()->route('onboarding');
        }

        // If no club is selected in session, use the first one
        if (! session()->has('current_club_id')) {
            $firstMembership = $memberships->first();
            session(['current_club_id' => $firstMembership->club_id]);
        }

        return $next($request);
    }
}

<?php

namespace App\Providers;

use App\Models\ClubMembership;
use App\Models\TeamMembership;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (!Auth::check() || !session('current_club_id')) {
                $view->with([
                    'isClubAdmin' => false,
                    'isCoachInClub' => false,
                ]);
                return;
            }

            $clubId = session('current_club_id');
            $userId = Auth::id();

            $clubRole = ClubMembership::where('club_id', $clubId)
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->value('role');

            $isClubAdmin = in_array($clubRole, ['owner', 'admin']);

            $isCoachInClub = TeamMembership::where('user_id', $userId)
                ->whereHas('team', fn($q) => $q->where('club_id', $clubId))
                ->whereIn('role', ['head_coach', 'assistant_coach'])
                ->exists();

            $isParent = Auth::user()->getChildrenIdsInClub($clubId)->isNotEmpty();

            $view->with([
                'isClubAdmin' => $isClubAdmin,
                'isCoachInClub' => $isCoachInClub,
                'isParent' => $isParent,
            ]);
        });
    }
}

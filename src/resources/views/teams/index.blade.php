@extends('layouts.app')

@section('title', __('messages.teams.title'))

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold">{{ __('messages.teams.title') }}</h1>
        @if($isClubAdmin)
            <a href="{{ route('teams.create') }}" class="btn-primary">{{ __('messages.teams.create') }}</a>
        @endif
    </div>

    @if($teams->isEmpty())
        <div class="card">
            <div class="card-body">
                <p class="text-muted">{{ __('messages.teams.no_teams') }}</p>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($teams as $team)
                <a href="{{ route('teams.show', $team) }}" class="card hover:ring-2 hover:ring-primary transition">
                    <div class="card-body">
                        <div class="flex items-center gap-3 mb-3">
                            @if($team->color)
                                <div class="w-4 h-4 rounded-full shrink-0" style="background-color: {{ $team->color }}"></div>
                            @endif
                            <h3 class="font-medium text-base">{{ $team->name }}</h3>
                        </div>

                        <div class="flex flex-wrap gap-2 mb-3">
                            @if($team->sport)
                                <span class="badge badge-primary">{{ $team->sport }}</span>
                            @endif
                            @if($team->age_category)
                                <span class="badge badge-gray">{{ $team->age_category }}</span>
                            @endif
                        </div>

                        <p class="text-sm text-muted">
                            {{ $team->team_memberships_count }} {{ __('messages.teams.member_count') }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection

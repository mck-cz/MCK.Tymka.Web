@extends('layouts.app')

@section('title', __('messages.events.title'))

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold">{{ __('messages.events.title') }}</h1>
        @if($isClubAdmin || $isCoachInClub)
            <a href="{{ route('events.create') }}" class="btn-primary">{{ __('messages.events.create') }}</a>
        @endif
    </div>

    {{-- Time filter tabs --}}
    <div class="flex gap-1 mb-4">
        <a href="{{ route('events.index', array_merge(request()->except('time'), ['time' => 'upcoming'])) }}"
            class="px-4 py-2 rounded-lg text-sm transition-colors {{ ($selectedTime ?? 'upcoming') === 'upcoming' ? 'bg-primary text-white' : 'bg-surface text-muted hover:bg-bg' }}">
            {{ __('messages.events.filter_upcoming') }}
        </a>
        <a href="{{ route('events.index', array_merge(request()->except('time'), ['time' => 'past'])) }}"
            class="px-4 py-2 rounded-lg text-sm transition-colors {{ ($selectedTime ?? 'upcoming') === 'past' ? 'bg-primary text-white' : 'bg-surface text-muted hover:bg-bg' }}">
            {{ __('messages.events.filter_past') }}
        </a>
        <a href="{{ route('events.index', array_merge(request()->except('time'), ['time' => 'all'])) }}"
            class="px-4 py-2 rounded-lg text-sm transition-colors {{ ($selectedTime ?? 'upcoming') === 'all' ? 'bg-primary text-white' : 'bg-surface text-muted hover:bg-bg' }}">
            {{ __('messages.events.filter_all') }}
        </a>
    </div>

    {{-- Filter bar --}}
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('events.index') }}" class="flex flex-wrap gap-4 items-end">
                <input type="hidden" name="time" value="{{ $selectedTime ?? 'upcoming' }}">
                <div>
                    <label for="event_type" class="form-label">{{ __('messages.common.filter') }}</label>
                    <select name="event_type" id="event_type" class="form-select">
                        <option value="">{{ __('messages.events.all_types') }}</option>
                        <option value="training" @selected($selectedType === 'training')>{{ __('messages.events.training') }}</option>
                        <option value="match" @selected($selectedType === 'match')>{{ __('messages.events.match') }}</option>
                        <option value="tournament" @selected($selectedType === 'tournament')>{{ __('messages.events.tournament') }}</option>
                        <option value="competition" @selected($selectedType === 'competition')>{{ __('messages.events.competition') }}</option>
                    </select>
                </div>

                <div>
                    <label for="team_id" class="form-label">{{ __('messages.events.team') }}</label>
                    <select name="team_id" id="team_id" class="form-select">
                        <option value="">{{ __('messages.events.all_teams') }}</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected($selectedTeamId === $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn-primary">{{ __('messages.common.filter') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Events list --}}
    @forelse ($events as $event)
        <a href="{{ route('events.show', $event) }}" class="card block mb-4 hover:ring-2 hover:ring-[var(--color-primary)] transition">
            <div class="card-body flex flex-col sm:flex-row sm:items-center gap-4">
                {{-- Date/time --}}
                <div class="shrink-0 text-center sm:text-left" style="min-width: 80px;">
                    <p class="text-lg font-semibold">{{ app()->getLocale() === 'cs' ? $event->starts_at->format('d.m.Y') : $event->starts_at->format('Y-m-d') }}</p>
                    <p class="text-sm text-muted">{{ $event->starts_at->format('H:i') }}@if($event->ends_at) - {{ $event->ends_at->format('H:i') }}@endif</p>
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-medium truncate">{{ $event->title }}</h3>
                        @switch($event->event_type)
                            @case('training')
                                <span class="badge badge-primary">{{ __('messages.events.training') }}</span>
                                @break
                            @case('match')
                                <span class="badge badge-accent">{{ __('messages.events.match') }}</span>
                                @break
                            @case('tournament')
                                <span class="badge badge-warning">{{ __('messages.events.tournament') }}</span>
                                @break
                            @case('competition')
                                <span class="badge badge-gray">{{ __('messages.events.competition') }}</span>
                                @break
                        @endswitch
                        @if($event->effective_status === 'in_progress')
                            <span class="badge badge-accent">{{ __('messages.events.in_progress') }}</span>
                        @elseif($event->effective_status === 'completed')
                            <span class="badge badge-gray">{{ __('messages.events.completed') }}</span>
                        @elseif($event->effective_status === 'past')
                            <span class="badge badge-warning">{{ __('messages.events.past_awaiting') }}</span>
                        @elseif($event->effective_status === 'cancelled')
                            <span class="badge badge-danger">{{ __('messages.events.cancelled') }}</span>
                        @endif
                        @if($event->effective_status === 'past' && !$event->attendance_recorded)
                            <span class="badge badge-danger">{{ __('messages.events.attendance_missing') }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-muted mt-1">
                        {{ $event->team->name }}
                        @if(isset($teamChildMap[$event->team_id]))
                            @foreach($teamChildMap[$event->team_id] as $childName)
                                @php
                                    $childRsvp = $eventChildRsvp[$event->id][$childName] ?? null;
                                    $badgeClass = match($childRsvp) {
                                        'confirmed' => 'badge-success',
                                        'declined' => 'badge-danger',
                                        default => 'badge-warning',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} text-xs">{{ $childName }}</span>
                            @endforeach
                        @endif
                        @if($event->venue)
                            &middot; {{ $event->venue->name }}
                        @endif
                    </p>
                </div>

                {{-- Attendance summary --}}
                <div class="shrink-0 text-sm text-muted">
                    {{ $event->attendances->where('rsvp_status', 'confirmed')->count() }} {{ __('messages.events.confirmed_count') }}
                    / {{ $event->attendances->count() }}
                </div>
            </div>
        </a>
    @empty
        <div class="card">
            <div class="card-body text-center">
                <p class="text-muted">{{ __('messages.events.no_events') }}</p>
            </div>
        </div>
    @endforelse
@endsection

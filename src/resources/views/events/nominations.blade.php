@extends('layouts.app')

@section('title', __('messages.nominations.title') . ' — ' . $event->title)

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.events.title'), 'href' => route('events.index')],
        ['label' => $event->title, 'href' => route('events.show', $event)],
        ['label' => __('messages.nominations.title')],
    ]" />

    <div class="mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.nominations.title') }} — {{ $event->title }}</h1>
        <p class="text-sm text-muted mt-1">{{ $event->team->name }} · {{ app()->getLocale() === 'cs' ? $event->starts_at->format('d.m.Y H:i') : $event->starts_at->format('Y-m-d H:i') }}</p>
    </div>

    {{-- Current nominations --}}
    <div class="card mb-6">
        <div class="card-header flex items-center justify-between">
            <h2 class="font-medium">{{ __('messages.nominations.title') }}</h2>
            @if($event->nominations->isNotEmpty())
                <span class="text-sm text-muted">{{ __('messages.nominations.count', ['count' => $event->nominations->count()]) }}</span>
            @endif
        </div>
        <div class="card-body">
            @forelse ($event->nominations->sortBy('teamMembership.user.last_name') as $nomination)
                <div class="flex items-center gap-3 py-2 @if(!$loop->last) border-b border-border @endif">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-medium">{{ $nomination->teamMembership->user->full_name }}</span>
                            @if($nomination->teamMembership->user->birth_date)
                                <span class="text-xs text-muted">({{ $nomination->teamMembership->user->birth_date->format('Y') }})</span>
                            @endif
                            @if($nomination->teamMembership->position)
                                <span class="badge badge-gray text-xs">{{ $nomination->teamMembership->position }}</span>
                            @endif
                            @if($nomination->teamMembership->jersey_number)
                                <span class="text-xs text-muted">#{{ $nomination->teamMembership->jersey_number }}</span>
                            @endif
                            @if($nomination->sourceTeam && $nomination->source_team_id !== $event->team_id)
                                <span class="badge badge-info text-xs">{{ $nomination->sourceTeam->name }}</span>
                            @endif
                        </div>
                    </div>

                    @if($nomination->status === 'nominated')
                        <span class="badge badge-warning">{{ __('messages.nominations.nominated') }}</span>
                    @elseif($nomination->status === 'accepted')
                        <span class="badge badge-success">{{ __('messages.nominations.accepted') }}</span>
                    @elseif($nomination->status === 'declined')
                        <span class="badge badge-danger">{{ __('messages.nominations.declined') }}</span>
                    @endif

                    <form method="POST" action="{{ route('nominations.destroy', $nomination) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-ghost text-sm text-danger" aria-label="{{ __('messages.common.delete') }}">&times;</button>
                    </form>
                </div>
            @empty
                <p class="text-muted">{{ __('messages.nominations.no_nominations') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Add nominations — grouped by team --}}
    <div class="card" x-data="{ selected: [] }">
        <div class="card-header flex items-center justify-between">
            <h2 class="font-medium">{{ __('messages.nominations.available_players') }}</h2>
            <span class="text-sm text-muted" x-show="selected.length > 0" x-text="selected.length + ' {{ __('messages.nominations.selected') }}'"></span>
        </div>
        <div class="card-body">
            @if($membersByTeam->isNotEmpty())
                <form method="POST" action="{{ route('nominations.store', $event) }}">
                    @csrf

                    @foreach($membersByTeam as $teamId => $members)
                        @php
                            $team = $clubTeams[$teamId] ?? null;
                            $isEventTeam = $teamId === $event->team_id;
                            $sortedMembers = $members->sortBy('user.last_name');
                        @endphp
                        <div class="mb-4 {{ !$loop->last ? 'pb-4 border-b border-border' : '' }}">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-sm font-semibold {{ $isEventTeam ? 'text-primary' : 'text-muted' }}">
                                    {{ $team->name ?? __('messages.common.unknown') }}
                                    @if($isEventTeam)
                                        <span class="font-normal text-xs">({{ __('messages.nominations.event_team') }})</span>
                                    @endif
                                </h3>
                                <button type="button" class="text-xs text-primary hover:underline"
                                    @click="document.querySelectorAll('[data-team=\'{{ $teamId }}\']').forEach(cb => { if (!cb.checked) { cb.checked = true; cb.dispatchEvent(new Event('change')); } })">
                                    {{ __('messages.nominations.select_all') }}
                                </button>
                            </div>

                            <div class="space-y-1">
                                @foreach ($sortedMembers as $member)
                                    <label class="flex items-center gap-3 py-1.5 px-2 rounded-lg hover:bg-bg cursor-pointer transition-colors">
                                        <input type="checkbox" name="team_membership_ids[]" value="{{ $member->id }}"
                                            class="form-checkbox" data-team="{{ $teamId }}"
                                            @change="selected = [...document.querySelectorAll('input[name=\'team_membership_ids[]\']:checked')].map(e => e.value)">
                                        <div class="flex items-center gap-2 flex-wrap flex-1 min-w-0">
                                            <span class="font-medium">{{ $member->user->full_name }}</span>
                                            @if($member->user->birth_date)
                                                <span class="text-xs text-muted">({{ $member->user->birth_date->format('Y') }})</span>
                                            @endif
                                            @if($member->position)
                                                <span class="badge badge-gray text-xs">{{ $member->position }}</span>
                                            @endif
                                            @if($member->jersey_number)
                                                <span class="text-xs text-muted">#{{ $member->jersey_number }}</span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    @error('team_membership_ids')
                        <p class="form-error mb-3">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="btn-primary" :disabled="selected.length === 0">{{ __('messages.nominations.nominate') }}</button>
                </form>
            @else
                <p class="text-muted">{{ __('messages.nominations.no_available') }}</p>
            @endif
        </div>
    </div>
@endsection

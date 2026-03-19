@extends('layouts.app')

@section('title', $event->title)

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <x-breadcrumb :items="[
        ['label' => __('messages.events.title'), 'href' => route('events.index')],
        ['label' => $event->title],
    ]" />

    {{-- Title + actions --}}
    <div class="mb-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-xl font-semibold">{{ $event->title }}</h1>
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
                @switch($event->effective_status)
                    @case('scheduled')
                        <span class="badge badge-success">{{ __('messages.events.scheduled') }}</span>
                        @break
                    @case('in_progress')
                        <span class="badge badge-accent">{{ __('messages.events.in_progress') }}</span>
                        @break
                    @case('completed')
                        <span class="badge badge-gray">{{ __('messages.events.completed') }}</span>
                        @break
                    @case('past')
                        <span class="badge badge-warning">{{ __('messages.events.past_awaiting') }}</span>
                        @if(!$event->attendance_recorded)
                            <span class="badge badge-danger">{{ __('messages.events.attendance_missing') }}</span>
                        @endif
                        @break
                    @case('cancelled')
                        <span class="badge badge-danger">{{ __('messages.events.cancelled') }}</span>
                        @break
                @endswitch
            </div>
            @if($canEdit ?? false)
                <div class="flex items-center gap-2">
                    @if($event->can_record_attendance)
                        <a href="{{ route('attendance-check.show', $event) }}" class="btn-primary text-sm">{{ __('messages.attendance_check.start') }}</a>
                    @endif
                    @if(in_array($event->effective_status, ['in_progress', 'past']) && $event->status !== 'completed')
                        <form action="{{ route('events.complete', $event) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn-primary text-sm">{{ __('messages.events.mark_completed') }}</button>
                        </form>
                    @endif
                    @if($event->team_id && $event->status === 'scheduled')
                        <form action="{{ route('events.sync-attendances', $event) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn-secondary text-sm">{{ __('messages.events.sync_attendances') }}</button>
                        </form>
                    @endif
                    <a href="{{ route('events.edit', $event) }}" class="btn-secondary text-sm">{{ __('messages.common.edit') }}</a>
                </div>
            @endif
        </div>
    </div>

    {{-- Info section --}}
    <div class="card mb-6">
        <div class="card-body space-y-3">
            <div class="flex gap-2">
                <span class="font-medium" style="min-width: 120px;">{{ __('messages.events.date_time') }}:</span>
                <span>{{ app()->getLocale() === 'cs' ? $event->starts_at->format('d.m.Y') : $event->starts_at->format('Y-m-d') }} {{ $event->starts_at->format('H:i') }}@if($event->ends_at) - {{ $event->ends_at->format('H:i') }}@endif</span>
            </div>
            @if($event->venue)
                <div class="flex gap-2">
                    <span class="font-medium" style="min-width: 120px;">{{ __('messages.events.venue') }}:</span>
                    <span>{{ $event->venue->name }}</span>
                </div>
            @endif
            <div class="flex gap-2">
                <span class="font-medium" style="min-width: 120px;">{{ __('messages.events.team') }}:</span>
                <span>
                    {{ $event->team->name }}
                    @if(isset($childAttendances) && $childAttendances->isNotEmpty())
                        @foreach($childAttendances as $ca)
                            <span class="badge badge-info text-xs ml-1">{{ $ca->teamMembership->user->first_name }}</span>
                        @endforeach
                    @endif
                </span>
            </div>
            @if(isset($coaches) && $coaches->isNotEmpty())
                <div class="flex gap-2">
                    <span class="font-medium" style="min-width: 120px;">{{ __('messages.events.coaches') }}:</span>
                    <span>
                        @foreach($coaches as $coach)
                            {{ $coach->user->full_name }}
                            <span class="text-xs text-muted">({{ __('messages.teams.' . $coach->role) }})</span>@if(!$loop->last), @endif
                        @endforeach
                    </span>
                </div>
            @endif
            @if($event->location)
                <div class="flex gap-2">
                    <span class="font-medium" style="min-width: 120px;">{{ __('messages.events.location') }}:</span>
                    <span>{{ $event->location }}</span>
                </div>
            @endif
            @if($event->rsvp_deadline)
                <div class="flex gap-2">
                    <span class="font-medium" style="min-width: 120px;">{{ __('messages.events.rsvp_deadline') }}:</span>
                    <span>{{ app()->getLocale() === 'cs' ? $event->rsvp_deadline->format('d.m.Y H:i') : $event->rsvp_deadline->format('Y-m-d H:i') }}</span>
                </div>
            @endif
            @if($event->min_capacity || $event->max_capacity)
                <div class="flex gap-2">
                    <span class="font-medium" style="min-width: 120px;">{{ __('messages.events.min_capacity') }} / {{ __('messages.events.max_capacity') }}:</span>
                    <span>{{ $event->min_capacity ?? '—' }} / {{ $event->max_capacity ?? '—' }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- User's RSVP (hide for matches if user has nomination — nomination controls attendance) --}}
    @php
        $hasNominations = in_array($event->event_type, ['match', 'tournament', 'competition']) && $event->nominations->isNotEmpty();
        $userHasNomination = $hasNominations && $event->nominations->contains(fn ($n) => $n->teamMembership->user_id === auth()->id());
    @endphp
    @if($userAttendance && !$userHasNomination)
        <div class="card mb-6" x-data="{ editing: {{ $userAttendance->rsvp_status === 'pending' ? 'true' : 'false' }} }">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <h2 class="font-medium">{{ __('messages.events.your_response') }}</h2>
                    @if($userAttendance->rsvp_status !== 'pending')
                        <button type="button" x-show="!editing" @click="editing = true" class="btn-ghost text-sm">{{ __('messages.rsvp.change') }}</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                {{-- Confirmed / Declined summary --}}
                <div x-show="!editing">
                    <div class="flex items-center gap-3">
                        @if($userAttendance->rsvp_status === 'confirmed')
                            <span class="text-success font-medium">&#10003; {{ __('messages.rsvp.confirmed') }}</span>
                        @elseif($userAttendance->rsvp_status === 'declined')
                            <span class="text-danger font-medium">&#10005; {{ __('messages.rsvp.declined') }}</span>
                        @endif
                        @if($userAttendance->responded_at)
                            <span class="text-xs text-muted">{{ app()->getLocale() === 'cs' ? $userAttendance->responded_at->format('d.m.Y H:i') : $userAttendance->responded_at->format('Y-m-d H:i') }}</span>
                        @endif
                    </div>
                    @if($userAttendance->rsvp_note)
                        <p class="text-sm text-muted mt-2">{{ $userAttendance->rsvp_note }}</p>
                    @endif
                </div>

                {{-- Edit form --}}
                <div x-show="editing" x-cloak>
                    @if($userAttendance->rsvp_status === 'pending')
                        <div class="mb-4">
                            <span class="text-muted font-medium">&#9201; {{ __('messages.rsvp.pending') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('attendances.update', $userAttendance) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label for="rsvp_note" class="form-label">{{ __('messages.events.note') }}</label>
                            <textarea name="rsvp_note" id="rsvp_note" rows="2" class="form-input w-full">{{ old('rsvp_note', $userAttendance->rsvp_note) }}</textarea>
                            @error('rsvp_note')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" name="rsvp_status" value="confirmed" class="btn-primary">
                                {{ __('messages.rsvp.confirm') }}
                            </button>
                            <button type="submit" name="rsvp_status" value="declined" class="btn-danger">
                                {{ __('messages.rsvp.decline') }}
                            </button>
                            @if($userAttendance->rsvp_status !== 'pending')
                                <button type="button" @click="editing = false" class="btn-ghost text-sm">{{ __('messages.common.cancel') }}</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Child RSVP forms (skip children who have nominations on match events) --}}
    @if(isset($childAttendances) && $childAttendances->isNotEmpty())
        @foreach($childAttendances as $childAttendance)
            @if($hasNominations && $event->nominations->contains(fn ($n) => $n->teamMembership->user_id === $childAttendance->teamMembership->user_id))
                @continue
            @endif
            <div class="card mb-6" x-data="{ editing: {{ $childAttendance->rsvp_status === 'pending' ? 'true' : 'false' }} }">
                <div class="card-header">
                    <div class="flex items-center justify-between">
                        <h2 class="font-medium">{{ __('messages.events.response_for_child', ['name' => $childAttendance->teamMembership->user->full_name]) }}</h2>
                        @if($childAttendance->rsvp_status !== 'pending')
                            <button type="button" x-show="!editing" @click="editing = true" class="btn-ghost text-sm">{{ __('messages.rsvp.change') }}</button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    {{-- Confirmed / Declined summary --}}
                    <div x-show="!editing">
                        <div class="flex items-center gap-3">
                            @if($childAttendance->rsvp_status === 'confirmed')
                                <span class="text-success font-medium">&#10003; {{ __('messages.rsvp.confirmed') }}</span>
                            @elseif($childAttendance->rsvp_status === 'declined')
                                <span class="text-danger font-medium">&#10005; {{ __('messages.rsvp.declined') }}</span>
                            @endif
                            @if($childAttendance->responded_at)
                                <span class="text-xs text-muted">{{ app()->getLocale() === 'cs' ? $childAttendance->responded_at->format('d.m.Y H:i') : $childAttendance->responded_at->format('Y-m-d H:i') }}</span>
                            @endif
                        </div>
                        @if($childAttendance->rsvp_note)
                            <p class="text-sm text-muted mt-2">{{ $childAttendance->rsvp_note }}</p>
                        @endif
                    </div>

                    {{-- Edit form --}}
                    <div x-show="editing" x-cloak>
                        @if($childAttendance->rsvp_status === 'pending')
                            <div class="mb-4">
                                <span class="text-muted font-medium">&#9201; {{ __('messages.rsvp.pending') }}</span>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('attendances.update', $childAttendance) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-4">
                                <label class="form-label">{{ __('messages.events.note') }}</label>
                                <textarea name="rsvp_note" rows="2" class="form-input w-full">{{ $childAttendance->rsvp_note }}</textarea>
                            </div>

                            <div class="flex gap-3">
                                <button type="submit" name="rsvp_status" value="confirmed" class="btn-primary">
                                    {{ __('messages.rsvp.confirm') }}
                                </button>
                                <button type="submit" name="rsvp_status" value="declined" class="btn-danger">
                                    {{ __('messages.rsvp.decline') }}
                                </button>
                                @if($childAttendance->rsvp_status !== 'pending')
                                    <button type="button" @click="editing = false" class="btn-ghost text-sm">{{ __('messages.common.cancel') }}</button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Nominations section (only for match/tournament/competition) --}}
    @if(in_array($event->event_type, ['match', 'tournament', 'competition']))
        <div class="card mb-6">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <h2 class="font-medium">
                        {{ __('messages.nominations.title') }}
                        <span class="text-muted font-normal text-sm">
                            ({{ __('messages.nominations.count', ['count' => $event->nominations->count()]) }})
                        </span>
                    </h2>
                    @if($canEdit ?? false)
                        <a href="{{ route('nominations.manage', $event) }}" class="btn-ghost text-sm">{{ __('messages.nominations.manage') }}</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @php
                    $relevantNominationIds = collect([auth()->id()])->merge(isset($childAttendances) ? $childAttendances->pluck('teamMembership.user_id') : collect());
                    $myNominations = $event->nominations->filter(fn ($n) => $relevantNominationIds->contains($n->teamMembership->user_id));
                @endphp

                @if($myNominations->isNotEmpty())
                    @foreach($myNominations as $myNom)
                        <div class="mb-4">
                            <p class="font-medium mb-2">
                                @if($myNom->teamMembership->user_id === auth()->id())
                                    {{ __('messages.nominations.your_nomination') }}
                                @else
                                    {{ __('messages.nominations.nomination_for', ['name' => $myNom->teamMembership->user->first_name]) }}
                                @endif
                            </p>
                            <div class="flex items-center gap-3">
                                @if($myNom->status === 'nominated')
                                    <span class="badge badge-warning">{{ __('messages.nominations.nominated') }}</span>
                                @elseif($myNom->status === 'accepted')
                                    <span class="badge badge-success">{{ __('messages.nominations.accepted') }}</span>
                                @elseif($myNom->status === 'declined')
                                    <span class="badge badge-danger">{{ __('messages.nominations.declined') }}</span>
                                @endif

                                @if($myNom->status === 'nominated')
                                    <form method="POST" action="{{ route('nominations.respond', $myNom) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" name="status" value="accepted" class="btn-primary text-sm">{{ __('messages.nominations.accept') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('nominations.respond', $myNom) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" name="status" value="declined" class="btn-danger text-sm">{{ __('messages.nominations.decline') }}</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif

                @forelse ($event->nominations as $nomination)
                    <div class="flex items-center gap-3 py-2 @if(!$loop->last) border-b border-border @endif">
                        <div class="flex-1">
                            <span class="font-medium">{{ $nomination->teamMembership->user->full_name }}</span>
                        </div>
                        @if($nomination->status === 'nominated')
                            <span class="badge badge-warning">{{ __('messages.nominations.nominated') }}</span>
                        @elseif($nomination->status === 'accepted')
                            <span class="badge badge-success">{{ __('messages.nominations.accepted') }}</span>
                        @elseif($nomination->status === 'declined')
                            <span class="badge badge-danger">{{ __('messages.nominations.declined') }}</span>
                        @endif
                    </div>
                @empty
                    <p class="text-muted">{{ __('messages.nominations.no_nominations') }}</p>
                @endforelse
            </div>
        </div>
    @endif

    {{-- Event Comments --}}
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.comments.title') }}</h2>
        </div>
        <div class="card-body">
            @if($event->eventComments && $event->eventComments->isNotEmpty())
                <div class="space-y-3 mb-4">
                    @foreach($event->eventComments->sortBy('created_at') as $comment)
                        <div class="flex gap-3">
                            <div class="w-7 h-7 rounded-full bg-primary-light text-primary flex items-center justify-center text-xs font-medium shrink-0 mt-0.5">
                                {{ strtoupper(mb_substr($comment->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($comment->user->last_name, 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-sm">{{ $comment->user->full_name }}</span>
                                    <span class="text-xs text-muted">{{ $comment->created_at->diffForHumans() }}</span>
                                    @if($comment->user_id === auth()->id())
                                        <form action="{{ route('event-comments.destroy', $comment) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-muted hover:text-danger">{{ __('messages.common.delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                                <p class="text-sm mt-0.5">{{ $comment->body }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('event-comments.store', $event) }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="body" placeholder="{{ __('messages.comments.placeholder') }}"
                    class="form-input w-full text-sm" required>
                <button type="submit" class="btn-primary text-sm shrink-0">{{ __('messages.messages.send') }}</button>
            </form>
        </div>
    </div>

    {{-- Match Result (only for match/tournament/competition) --}}
    @if(in_array($event->event_type, ['match', 'tournament', 'competition']))
        <div class="card mb-6">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <h2 class="font-medium">{{ __('messages.results.title') }}</h2>
                    @if($event->eventResult && ($canManageResult ?? false))
                        <form action="{{ route('event-results.destroy', $event->eventResult) }}" method="POST"
                            onsubmit="return confirm('{{ __('messages.results.delete_confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-muted hover:text-danger">{{ __('messages.common.delete') }}</button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($event->eventResult)
                    <div class="space-y-2">
                        @if($event->eventResult->opponent_name)
                            <div class="flex gap-2">
                                <span class="font-medium" style="min-width: 120px;">{{ __('messages.results.opponent') }}:</span>
                                <span>{{ $event->eventResult->opponent_name }}</span>
                            </div>
                        @endif
                        @if($event->eventResult->score_home !== null && $event->eventResult->score_away !== null)
                            <div class="flex gap-2">
                                <span class="font-medium" style="min-width: 120px;">{{ __('messages.results.score') }}:</span>
                                <span class="text-lg font-semibold">{{ $event->eventResult->score_home }} : {{ $event->eventResult->score_away }}</span>
                            </div>
                        @endif
                        @if($event->eventResult->result)
                            <div class="flex gap-2">
                                <span class="font-medium" style="min-width: 120px;">{{ __('messages.results.result') }}:</span>
                                @if($event->eventResult->result === 'win')
                                    <span class="badge badge-success">{{ __('messages.results.win') }}</span>
                                @elseif($event->eventResult->result === 'loss')
                                    <span class="badge badge-danger">{{ __('messages.results.loss') }}</span>
                                @else
                                    <span class="badge badge-gray">{{ __('messages.results.draw') }}</span>
                                @endif
                            </div>
                        @endif
                        @if($event->eventResult->notes)
                            <div class="flex gap-2">
                                <span class="font-medium" style="min-width: 120px;">{{ __('messages.results.notes') }}:</span>
                                <span>{{ $event->eventResult->notes }}</span>
                            </div>
                        @endif
                    </div>
                @elseif(!($canManageResult ?? false))
                    <p class="text-muted">{{ __('messages.results.no_result') }}</p>
                @endif

                {{-- Result form (coach/admin only) --}}
                @if($canManageResult ?? false)
                    <div class="@if($event->eventResult) mt-4 pt-4 border-t border-border @endif">
                        <h3 class="font-medium text-sm mb-3">{{ $event->eventResult ? __('messages.results.update') : __('messages.results.record') }}</h3>
                        <form method="POST" action="{{ route('event-results.store', $event) }}">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="opponent_name" class="form-label">{{ __('messages.results.opponent') }}</label>
                                    <input type="text" name="opponent_name" id="opponent_name"
                                        value="{{ old('opponent_name', $event->eventResult?->opponent_name) }}"
                                        class="form-input w-full" maxlength="255">
                                    @error('opponent_name')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="result" class="form-label">{{ __('messages.results.result') }}</label>
                                    <select name="result" id="result" class="form-input w-full">
                                        <option value="">—</option>
                                        <option value="win" @selected(old('result', $event->eventResult?->result) === 'win')>{{ __('messages.results.win') }}</option>
                                        <option value="loss" @selected(old('result', $event->eventResult?->result) === 'loss')>{{ __('messages.results.loss') }}</option>
                                        <option value="draw" @selected(old('result', $event->eventResult?->result) === 'draw')>{{ __('messages.results.draw') }}</option>
                                    </select>
                                    @error('result')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="score_home" class="form-label">{{ __('messages.results.score_home') }}</label>
                                    <input type="number" name="score_home" id="score_home" min="0"
                                        value="{{ old('score_home', $event->eventResult?->score_home) }}"
                                        class="form-input w-full">
                                    @error('score_home')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="score_away" class="form-label">{{ __('messages.results.score_away') }}</label>
                                    <input type="number" name="score_away" id="score_away" min="0"
                                        value="{{ old('score_away', $event->eventResult?->score_away) }}"
                                        class="form-input w-full">
                                    @error('score_away')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="result_notes" class="form-label">{{ __('messages.results.notes') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                                <textarea name="notes" id="result_notes" rows="2" class="form-input w-full" maxlength="1000">{{ old('notes', $event->eventResult?->notes) }}</textarea>
                                @error('notes')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Attendance list --}}
    @php
        // For matches with nominations, only show nominated athletes
        if ($hasNominations) {
            $nominatedMembershipIds = $event->nominations->pluck('team_membership_id');
            $filteredAthleteAttendances = $athleteAttendances->filter(
                fn ($a) => $nominatedMembershipIds->contains($a->team_membership_id)
            );
        } else {
            $filteredAthleteAttendances = $athleteAttendances;
        }
    @endphp
    <div class="card">
        <div class="card-header">
            <h2 class="font-medium">
                {{ $hasNominations ? __('messages.nominations.title') . ' — ' : '' }}{{ __('messages.events.attendance') }}
                <span class="text-muted font-normal text-sm">
                    ({{ $filteredAthleteAttendances->where('rsvp_status', 'confirmed')->count() }} {{ __('messages.events.confirmed_count') }} / {{ $filteredAthleteAttendances->count() }})
                </span>
            </h2>
        </div>
        <div class="card-body">
            {{-- Athletes --}}
            @forelse ($filteredAthleteAttendances as $attendance)
                @include('events._attendance-row', ['attendance' => $attendance, 'isLast' => $loop->last])
            @empty
                <p class="text-muted">{{ __('messages.common.no_results') }}</p>
            @endforelse

            {{-- Coaches --}}
            @if($coachAttendances->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-border">
                    <p class="text-xs text-muted font-medium uppercase tracking-wide mb-2">{{ __('messages.events.coaches') }}</p>
                    @foreach($coachAttendances as $attendance)
                        @include('events._attendance-row', ['attendance' => $attendance, 'isLast' => $loop->last])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

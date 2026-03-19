@extends('layouts.app')

@section('title', __('messages.dashboard.title'))

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.dashboard.welcome') }}, {{ Auth::user()->full_name }}</h1>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="card">
            <div class="card-body">
                <p class="text-3xl font-semibold">{{ $upcomingEvents->count() }}</p>
                <p class="text-sm text-muted mt-1">{{ __('messages.dashboard.events_count', ['count' => $upcomingEvents->count()]) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-3xl font-semibold">{{ $teams->count() }}</p>
                <p class="text-sm text-muted mt-1">{{ __('messages.dashboard.teams_count', ['count' => $teams->count()]) }}</p>
            </div>
        </div>
        <div class="card" x-data="{ count: {{ $pendingAttendances->count() }} }" @pending-updated.window="count = $event.detail">
            <div class="card-body">
                <p class="text-3xl font-semibold" x-text="count"></p>
                <p class="text-sm text-muted mt-1" x-text="count + ' {{ __('messages.dashboard.pending_label') }}'"></p>
            </div>
        </div>
        <a href="{{ route('notifications.index') }}" class="card hover:bg-bg transition-colors">
            <div class="card-body">
                <p class="text-3xl font-semibold">{{ $unreadCount }}</p>
                <p class="text-sm text-muted mt-1">{{ __('messages.dashboard.unread_notifications') }}</p>
            </div>
        </a>
    </div>

    {{-- Coach quick actions --}}
    @if($isCoach)
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="font-medium">{{ __('messages.dashboard.quick_actions') }}</h3>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('events.create') }}" class="btn-primary text-sm">{{ __('messages.events.create') }}</a>
                    <a href="{{ route('statistics.index') }}" class="btn-secondary text-sm">{{ __('messages.statistics.title') }}</a>
                    <a href="{{ route('recurrence-rules.index') }}" class="btn-secondary text-sm">{{ __('messages.recurrence.title') }}</a>
                </div>
            </div>
        </div>
    @endif

    {{-- Pending payments --}}
    @if($pendingPayments->isNotEmpty())
        <div class="card mb-6">
            <div class="card-header flex items-center justify-between">
                <h3 class="font-medium">{{ __('messages.dashboard.pending_payments') }}</h3>
                <a href="{{ route('payments.index') }}" class="text-sm text-primary hover:underline">{{ __('messages.dashboard.view_all') }}</a>
            </div>
            <div class="card-body">
                @foreach($pendingPayments as $payment)
                    <div class="flex items-center justify-between {{ !$loop->last ? 'mb-3 pb-3 border-b border-border' : '' }}">
                        <div>
                            <p class="font-medium">{{ $payment->paymentRequest->name }}</p>
                            <p class="text-sm text-muted">
                                {{ number_format($payment->paymentRequest->amount, 0) }} {{ $payment->paymentRequest->currency ?? 'CZK' }}
                                @if($payment->status === 'overdue')
                                    <span class="text-danger font-medium">· {{ __('messages.payments.overdue') }}</span>
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('payments.show', $payment->paymentRequest) }}" class="btn-ghost text-sm">{{ __('messages.dashboard.view_all') }}</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Children widget --}}
    @if($childrenData->isNotEmpty())
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="font-medium">{{ __('messages.dashboard.my_children') }}</h3>
            </div>
            <div class="card-body">
                @foreach($childrenData as $data)
                    <div class="flex items-center justify-between {{ !$loop->last ? 'mb-3 pb-3 border-b border-border' : '' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-accent-light text-accent flex items-center justify-center text-xs font-medium shrink-0">
                                {{ $data->child->initials }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ $data->child->full_name }}</p>
                                <p class="text-xs text-muted truncate">
                                    @foreach($data->teams as $team)
                                        {{ $team->name }}@if($multiClub ?? false) ({{ $team->club->name ?? '' }})@endif@if(!$loop->last), @endif
                                    @endforeach
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 ml-3">
                            @if($data->pendingCount > 0)
                                <span class="badge badge-warning">{{ $data->pendingCount }} {{ __('messages.dashboard.pending_short') }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Upcoming events --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="font-medium">{{ __('messages.dashboard.upcoming_events') }}</h3>
                <a href="{{ route('events.index') }}" class="text-sm text-primary hover:underline">{{ __('messages.dashboard.view_all') }}</a>
            </div>
            <div class="card-body">
                @forelse($upcomingEvents as $event)
                    <a href="{{ route('events.show', $event) }}" class="flex items-start gap-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-border' : '' }} hover:bg-bg rounded-lg -mx-2 px-2 py-1 transition-colors">
                        <div class="text-center shrink-0 w-12">
                            <p class="text-sm font-semibold">
                                {{ app()->getLocale() === 'cs' ? $event->starts_at->format('d.m.') : $event->starts_at->format('M d') }}
                            </p>
                            <p class="text-xs text-muted">{{ $event->starts_at->format('H:i') }}</p>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium truncate">{{ $event->title }}</p>
                            <p class="text-sm text-muted truncate">
                                {{ $event->team->name }}
                                @if($multiClub ?? false)
                                    <span class="opacity-60">· {{ $event->team->club->name ?? '' }}</span>
                                @endif
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
                                    &middot; {{ __('messages.dashboard.event_at') }} {{ $event->venue->name }}
                                @endif
                            </p>
                        </div>
                        <div class="shrink-0">
                            @switch($event->event_type)
                                @case('training')
                                    <span class="badge badge-primary">{{ __('messages.events.training') }}</span>
                                    @break
                                @case('match')
                                    <span class="badge badge-accent">{{ __('messages.events.match') }}</span>
                                    @break
                                @case('competition')
                                    <span class="badge badge-gray">{{ __('messages.events.competition') }}</span>
                                    @break
                                @case('tournament')
                                    <span class="badge badge-warning">{{ __('messages.events.tournament') }}</span>
                                    @break
                            @endswitch
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-muted">{{ __('messages.dashboard.no_events') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Right column --}}
        <div class="space-y-4">
            {{-- Pending responses (grouped by event) --}}
            <div class="card" x-data="pendingRsvp()" x-cloak>
                <div class="card-header">
                    <h3 class="font-medium">
                        {{ __('messages.dashboard.pending_responses') }}
                        <span class="text-muted font-normal text-sm" x-show="pendingCount > 0" x-text="'(' + pendingCount + ')'"></span>
                    </h3>
                </div>
                <div class="card-body">
                    <template x-if="events.length === 0">
                        <p class="text-sm text-muted">{{ __('messages.dashboard.no_pending') }}</p>
                    </template>
                    <template x-for="(ev, idx) in events" :key="ev.id">
                        <div :class="idx < events.length - 1 ? 'mb-3 pb-3 border-b border-border' : ''" class="flex items-center justify-between">
                            <a :href="ev.url" class="min-w-0 flex-1 hover:opacity-80 transition-opacity">
                                <p class="font-medium truncate">
                                    <span x-text="ev.title"></span>
                                    <template x-for="att in ev.attendances.filter(a => a.isChild)" :key="att.id">
                                        <span class="badge badge-info text-xs ml-1" x-text="att.firstName"></span>
                                    </template>
                                </p>
                                <p class="text-sm text-muted">
                                    <span x-text="ev.date + ' ' + ev.time"></span>
                                    &middot;
                                    <span x-text="ev.team"></span>
                                    <template x-if="ev.club">
                                        <span class="opacity-60" x-text="'· ' + ev.club"></span>
                                    </template>
                                </p>
                            </a>
                            <button
                                type="button"
                                class="btn-primary text-xs px-3 py-1.5 shrink-0 ml-3"
                                @click="openModal(ev)"
                            >{{ __('messages.dashboard.respond') }}</button>
                        </div>
                    </template>
                </div>

                {{-- Quick RSVP Modal --}}
                <template x-teleport="body">
                    <div
                        x-show="modal"
                        x-transition.opacity
                        class="fixed inset-0 z-50 flex items-center justify-center p-4"
                        @keydown.escape.window="modal = false"
                        style="display: none;"
                    >
                        <div class="fixed inset-0 bg-black/40" @click="modal = false"></div>
                        <div class="relative bg-surface rounded-2xl shadow-lg w-full max-w-md" @click.stop>
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold" x-text="currentEvent?.title"></h3>
                                    <button @click="modal = false" class="text-muted hover:text-text text-xl leading-none">&times;</button>
                                </div>
                                <p class="text-sm text-muted mb-5">
                                    <span x-text="currentEvent?.date"></span>
                                    <span x-text="currentEvent?.time"></span>
                                    &middot;
                                    <span x-text="currentEvent?.team"></span>
                                </p>

                                <div class="space-y-0 mb-6">
                                    <template x-for="att in currentEvent?.attendances ?? []" :key="att.id">
                                        <div class="flex items-center justify-between py-3 border-b border-border">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium" x-text="att.name"></span>
                                                <template x-if="att.isChild">
                                                    <span class="badge badge-info text-xs">{{ __('messages.dashboard.child_badge') }}</span>
                                                </template>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <button
                                                    type="button"
                                                    class="w-9 h-9 rounded-lg flex items-center justify-center text-sm transition-colors"
                                                    :class="responses[att.id] === 'confirmed' ? 'bg-primary text-white' : 'bg-bg text-muted hover:bg-primary-light'"
                                                    @click="responses[att.id] = 'confirmed'"
                                                >&#10003;</button>
                                                <button
                                                    type="button"
                                                    class="w-9 h-9 rounded-lg flex items-center justify-center text-sm transition-colors"
                                                    :class="responses[att.id] === 'declined' ? 'bg-danger text-white' : 'bg-bg text-muted hover:bg-accent-light'"
                                                    @click="responses[att.id] = 'declined'"
                                                >&#10005;</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button
                                        type="button"
                                        class="btn-ghost text-sm flex-1"
                                        @click="currentEvent?.attendances.forEach(a => responses[a.id] = 'confirmed')"
                                    >{{ __('messages.dashboard.confirm_all') }}</button>
                                    <button
                                        type="button"
                                        class="btn-primary text-sm flex-1"
                                        :disabled="submitting"
                                        @click="submitResponses()"
                                        x-text="submitting ? '...' : '{{ __('messages.dashboard.send_responses') }}'"
                                    ></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <script>
                function pendingRsvp() {
                    return {
                        events: @js($pendingByEvent->map(function ($attendances, $eventId) use ($multiClub) {
                            $event = $attendances->first()->event;
                            return [
                                'id' => $eventId,
                                'title' => $event->title,
                                'url' => route('events.show', $eventId),
                                'date' => app()->getLocale() === 'cs' ? $event->starts_at->format('d.m.') : $event->starts_at->format('M d'),
                                'time' => $event->starts_at->format('H:i'),
                                'team' => $event->team->name,
                                'club' => $multiClub ? ($event->team->club->name ?? null) : null,
                                'attendances' => $attendances->map(fn ($a) => [
                                    'id' => $a->id,
                                    'name' => $a->teamMembership->user->full_name,
                                    'firstName' => $a->teamMembership->user->first_name,
                                    'isChild' => $a->teamMembership->user_id !== auth()->id(),
                                ])->values(),
                            ];
                        })->values()),
                        modal: false,
                        currentEvent: null,
                        responses: {},
                        submitting: false,
                        get pendingCount() {
                            return this.events.reduce((sum, ev) => sum + ev.attendances.length, 0);
                        },
                        openModal(ev) {
                            this.currentEvent = ev;
                            this.responses = {};
                            ev.attendances.forEach(a => this.responses[a.id] = 'confirmed');
                            this.modal = true;
                        },
                        async submitResponses() {
                            this.submitting = true;
                            const payload = this.currentEvent.attendances.map(a => ({
                                id: a.id,
                                rsvp_status: this.responses[a.id],
                            }));
                            try {
                                const res = await fetch('{{ route('attendances.batch') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    },
                                    body: JSON.stringify({ attendances: payload }),
                                });
                                if (res.ok) {
                                    this.events = this.events.filter(ev => ev.id !== this.currentEvent.id);
                                    this.$dispatch('pending-updated', this.pendingCount);
                                    this.modal = false;
                                    this.currentEvent = null;
                                }
                            } finally {
                                this.submitting = false;
                            }
                        },
                    };
                }
            </script>

            {{-- My teams --}}
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-medium">{{ __('messages.dashboard.my_teams') }}</h3>
                    <a href="{{ route('teams.index') }}" class="text-sm text-primary hover:underline">{{ __('messages.dashboard.view_all') }}</a>
                </div>
                <div class="card-body">
                    @forelse($teams as $team)
                        <div class="flex items-center justify-between {{ !$loop->last ? 'mb-3 pb-3 border-b border-border' : '' }}">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($team->color)
                                    <div class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $team->color }}"></div>
                                @endif
                                <p class="font-medium truncate">
                                    {{ $team->name }}
                                    @if($multiClub ?? false)
                                        <span class="text-xs text-muted font-normal">· {{ $team->club->name ?? '' }}</span>
                                    @endif
                                </p>
                            </div>
                            <span class="text-sm text-muted shrink-0 ml-3">{{ $team->team_memberships_count }} {{ __('messages.teams.members') }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-muted">{{ __('messages.common.no_results') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

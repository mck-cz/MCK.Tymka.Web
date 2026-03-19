@extends('layouts.app')

@section('title', __('messages.statistics.title'))

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold">{{ __('messages.statistics.title') }}</h1>
        @if($selectedTeam)
            <a href="{{ route('statistics.export', ['team_id' => $selectedTeamId]) }}" class="btn-secondary text-sm">{{ __('messages.statistics.export_csv') }}</a>
        @endif
    </div>

    {{-- Team selector --}}
    @if($teams->count() > 1)
        <div class="mb-6">
            <form method="GET" action="{{ route('statistics.index') }}" class="flex items-center gap-3">
                <label for="team_id" class="form-label mb-0">{{ __('messages.events.team') }}:</label>
                <select name="team_id" id="team_id" class="form-select" onchange="this.form.submit()">
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}" @selected($team->id === $selectedTeamId)>{{ $team->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    @if($selectedTeam)
        {{-- Summary cards --}}
        @php
            $totalMembers = count($stats);
            $avgAttendance = $totalMembers > 0 ? round(collect($stats)->avg('attendance_rate')) : 0;
            $avgRsvp = $totalMembers > 0 ? round(collect($stats)->avg('rsvp_rate')) : 0;
            $totalEventsCount = !empty($stats) ? $stats[0]['total_events'] : 0;
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-2xl font-semibold text-primary">{{ $totalEventsCount }}</div>
                    <div class="text-xs text-muted">{{ __('messages.statistics.total_events') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-2xl font-semibold text-primary">{{ $totalMembers }}</div>
                    <div class="text-xs text-muted">{{ __('messages.statistics.total_members') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-2xl font-semibold {{ $avgAttendance >= 70 ? 'text-success' : ($avgAttendance >= 40 ? 'text-warning' : 'text-danger') }}">{{ $avgAttendance }}%</div>
                    <div class="text-xs text-muted">{{ __('messages.statistics.avg_attendance') }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-2xl font-semibold text-primary">{{ $avgRsvp }}%</div>
                    <div class="text-xs text-muted">{{ __('messages.statistics.avg_rsvp') }}</div>
                </div>
            </div>
        </div>

        {{-- Per-member table --}}
        <div class="card">
            <div class="card-header">
                <h2 class="font-medium">{{ __('messages.statistics.member_stats') }}</h2>
            </div>
            <div class="card-body">
                @if(empty($stats))
                    <p class="text-muted">{{ __('messages.common.no_results') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border text-left">
                                    <th class="pb-2 font-medium">{{ __('messages.club_admin.member_name') }}</th>
                                    <th class="pb-2 font-medium text-center">{{ __('messages.statistics.rsvp_confirmed') }}</th>
                                    <th class="pb-2 font-medium text-center">{{ __('messages.statistics.present') }}</th>
                                    <th class="pb-2 font-medium text-center">{{ __('messages.statistics.absent') }}</th>
                                    <th class="pb-2 font-medium text-center">{{ __('messages.statistics.attendance_rate') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats as $stat)
                                    <tr class="border-b border-border last:border-0">
                                        <td class="py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-xs font-medium shrink-0">
                                                    {{ strtoupper(mb_substr($stat['member']->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($stat['member']->user->last_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="font-medium">{{ $stat['member']->user->full_name }}</span>
                                                    @if($stat['member']->role !== 'athlete')
                                                        <span class="badge badge-primary text-xs ml-1">
                                                            {{ __('messages.teams.' . $stat['member']->role) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="text-success">{{ $stat['confirmed'] }}</span>
                                            <span class="text-muted">/{{ $stat['total_events'] }}</span>
                                        </td>
                                        <td class="py-3 text-center text-success">{{ $stat['present'] }}</td>
                                        <td class="py-3 text-center text-danger">{{ $stat['absent'] }}</td>
                                        <td class="py-3 text-center">
                                            @if($stat['checked_events'] > 0)
                                                <span class="font-medium {{ $stat['attendance_rate'] >= 70 ? 'text-success' : ($stat['attendance_rate'] >= 40 ? 'text-warning' : 'text-danger') }}">
                                                    {{ $stat['attendance_rate'] }}%
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <p class="text-muted">{{ __('messages.teams.no_teams') }}</p>
            </div>
        </div>
    @endif
@endsection

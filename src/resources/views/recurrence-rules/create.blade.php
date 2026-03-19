@extends('layouts.app')

@section('title', __('messages.recurrence.create'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.recurrence.title'), 'href' => route('recurrence-rules.index')],
        ['label' => __('messages.recurrence.create')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.recurrence.create') }}</h1>

    <div class="card max-w-2xl">
        <div class="card-body">
            <form action="{{ route('recurrence-rules.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="form-label">{{ __('messages.recurrence.rule_name') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="form-input w-full @error('name') border-danger @enderror">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="team_id" class="form-label">{{ __('messages.events.team') }}</label>
                    <select name="team_id" id="team_id" class="form-select w-full @error('team_id') border-danger @enderror" required>
                        <option value="">{{ __('messages.events.select_team') }}</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('team_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="event_type" class="form-label">{{ __('messages.events.type') }}</label>
                    <select name="event_type" id="event_type" class="form-select w-full @error('event_type') border-danger @enderror" required>
                        <option value="training" @selected(old('event_type') === 'training')>{{ __('messages.events.training') }}</option>
                        <option value="match" @selected(old('event_type') === 'match')>{{ __('messages.events.match') }}</option>
                        <option value="competition" @selected(old('event_type') === 'competition')>{{ __('messages.events.competition') }}</option>
                        <option value="tournament" @selected(old('event_type') === 'tournament')>{{ __('messages.events.tournament') }}</option>
                    </select>
                    @error('event_type')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="frequency" class="form-label">{{ __('messages.recurrence.frequency') }}</label>
                    <select name="frequency" id="frequency" class="form-select w-full @error('frequency') border-danger @enderror" required>
                        <option value="weekly" @selected(old('frequency') === 'weekly')>{{ __('messages.recurrence.freq_weekly') }}</option>
                        <option value="biweekly" @selected(old('frequency') === 'biweekly')>{{ __('messages.recurrence.freq_biweekly') }}</option>
                        <option value="monthly" @selected(old('frequency') === 'monthly')>{{ __('messages.recurrence.freq_monthly') }}</option>
                    </select>
                    @error('frequency')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="day_of_week" class="form-label">{{ __('messages.recurrence.day') }}</label>
                    <select name="day_of_week" id="day_of_week" class="form-select w-full @error('day_of_week') border-danger @enderror" required>
                        @for($i = 0; $i <= 6; $i++)
                            <option value="{{ $i }}" @selected(old('day_of_week') == $i)>{{ __('messages.recurrence.day_' . $i) }}</option>
                        @endfor
                    </select>
                    @error('day_of_week')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="time_start" class="form-label">{{ __('messages.events.start') }}</label>
                        <input type="time" name="time_start" id="time_start" value="{{ old('time_start') }}"
                            class="form-input w-full @error('time_start') border-danger @enderror" required>
                        @error('time_start')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="time_end" class="form-label">{{ __('messages.events.end') }}</label>
                        <input type="time" name="time_end" id="time_end" value="{{ old('time_end') }}"
                            class="form-input w-full @error('time_end') border-danger @enderror" required>
                        @error('time_end')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="venue_id" class="form-label">{{ __('messages.events.venue') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <select name="venue_id" id="venue_id" class="form-select w-full @error('venue_id') border-danger @enderror">
                        <option value="">{{ __('messages.events.select_venue') }}</option>
                        @foreach($venues as $venue)
                            <option value="{{ $venue->id }}" @selected(old('venue_id') == $venue->id)>{{ $venue->name }}</option>
                        @endforeach
                    </select>
                    @error('venue_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="valid_from" class="form-label">{{ __('messages.recurrence.valid_from') }}</label>
                        <input type="date" name="valid_from" id="valid_from" value="{{ old('valid_from') }}"
                            class="form-input w-full @error('valid_from') border-danger @enderror" required>
                        @error('valid_from')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="valid_until" class="form-label">{{ __('messages.recurrence.valid_until') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                        <input type="date" name="valid_until" id="valid_until" value="{{ old('valid_until') }}"
                            class="form-input w-full @error('valid_until') border-danger @enderror">
                        @error('valid_until')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="auto_create_days_ahead" class="form-label">{{ __('messages.recurrence.auto_create_days') }}</label>
                    <input type="number" name="auto_create_days_ahead" id="auto_create_days_ahead"
                        value="{{ old('auto_create_days_ahead', 14) }}" min="1" max="90"
                        class="form-input w-full @error('auto_create_days_ahead') border-danger @enderror" required>
                    @error('auto_create_days_ahead')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
                    <a href="{{ route('recurrence-rules.index') }}" class="btn-secondary">{{ __('messages.common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

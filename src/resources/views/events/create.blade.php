@extends('layouts.app')

@section('title', __('messages.events.create_title'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.events.title'), 'href' => route('events.index')],
        ['label' => __('messages.events.create_title')],
    ]" />

    <div class="mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.events.create_title') }}</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('events.store') }}" class="space-y-4">
                @csrf

                {{-- Title --}}
                <div>
                    <label for="title" class="form-label">{{ __('messages.events.title_field') }}</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" class="form-input w-full" required>
                    @error('title')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Event type --}}
                <div>
                    <label for="event_type" class="form-label">{{ __('messages.events.type') }}</label>
                    <select name="event_type" id="event_type" class="form-select w-full" required>
                        <option value="">--</option>
                        <option value="training" @selected(old('event_type') === 'training')>{{ __('messages.events.training') }}</option>
                        <option value="match" @selected(old('event_type') === 'match')>{{ __('messages.events.match') }}</option>
                        <option value="competition" @selected(old('event_type') === 'competition')>{{ __('messages.events.competition') }}</option>
                        <option value="tournament" @selected(old('event_type') === 'tournament')>{{ __('messages.events.tournament') }}</option>
                    </select>
                    @error('event_type')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Team --}}
                <div>
                    <label for="team_id" class="form-label">{{ __('messages.events.team') }}</label>
                    <select name="team_id" id="team_id" class="form-select w-full" required>
                        <option value="">{{ __('messages.events.select_team') }}</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id') === $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('team_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Venue --}}
                <div>
                    <label for="venue_id" class="form-label">{{ __('messages.events.venue') }} <span class="text-muted text-sm">({{ __('messages.common.optional') }})</span></label>
                    <select name="venue_id" id="venue_id" class="form-select w-full">
                        <option value="">{{ __('messages.events.select_venue') }}</option>
                        @foreach ($venues as $venue)
                            <option value="{{ $venue->id }}" @selected(old('venue_id') === $venue->id)>{{ $venue->name }}</option>
                        @endforeach
                    </select>
                    @error('venue_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Location --}}
                <div>
                    <label for="location" class="form-label">{{ __('messages.events.location') }} <span class="text-muted text-sm">({{ __('messages.common.optional') }})</span></label>
                    <input type="text" name="location" id="location" value="{{ old('location') }}" class="form-input w-full">
                    @error('location')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Starts at --}}
                <div>
                    <label for="starts_at" class="form-label">{{ __('messages.events.start') }}</label>
                    <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at') }}" class="form-input w-full" required>
                    @error('starts_at')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Ends at --}}
                <div>
                    <label for="ends_at" class="form-label">{{ __('messages.events.end') }} <span class="text-muted text-sm">({{ __('messages.common.optional') }})</span></label>
                    <input type="datetime-local" name="ends_at" id="ends_at" value="{{ old('ends_at') }}" class="form-input w-full">
                    @error('ends_at')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- RSVP deadline --}}
                <div>
                    <label for="rsvp_deadline" class="form-label">{{ __('messages.events.rsvp_deadline') }} <span class="text-muted text-sm">({{ __('messages.common.optional') }})</span></label>
                    <input type="datetime-local" name="rsvp_deadline" id="rsvp_deadline" value="{{ old('rsvp_deadline') }}" class="form-input w-full">
                    @error('rsvp_deadline')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Capacity --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="min_capacity" class="form-label">{{ __('messages.events.min_capacity') }} <span class="text-muted text-sm">({{ __('messages.common.optional') }})</span></label>
                        <input type="number" name="min_capacity" id="min_capacity" value="{{ old('min_capacity') }}" class="form-input w-full" min="1">
                        @error('min_capacity')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="max_capacity" class="form-label">{{ __('messages.events.max_capacity') }} <span class="text-muted text-sm">({{ __('messages.common.optional') }})</span></label>
                        <input type="number" name="max_capacity" id="max_capacity" value="{{ old('max_capacity') }}" class="form-input w-full" min="1">
                        @error('max_capacity')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label for="notes" class="form-label">{{ __('messages.events.notes') }} <span class="text-muted text-sm">({{ __('messages.common.optional') }})</span></label>
                    <textarea name="notes" id="notes" rows="3" class="form-input w-full">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Instructions --}}
                <div>
                    <label for="instructions" class="form-label">{{ __('messages.events.instructions') }} <span class="text-muted text-sm">({{ __('messages.common.optional') }})</span></label>
                    <textarea name="instructions" id="instructions" rows="3" class="form-input w-full">{{ old('instructions') }}</textarea>
                    @error('instructions')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

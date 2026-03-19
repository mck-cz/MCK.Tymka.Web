@extends('layouts.app')

@section('title', __('messages.calendar_feeds.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <h1 class="text-xl font-semibold mb-2">{{ __('messages.calendar_feeds.title') }}</h1>
    <p class="text-sm text-muted mb-6">{{ __('messages.calendar_feeds.description') }}</p>

    {{-- Existing feeds --}}
    @forelse($feeds as $feed)
        <div class="card mb-4">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ $feed->name }}</span>
                            @if($feed->is_default)
                                <span class="badge badge-gray">{{ __('messages.calendar_feeds.default') }}</span>
                            @endif
                            @if($feed->is_active)
                                <span class="badge badge-success">{{ __('messages.calendar_feeds.active') }}</span>
                            @else
                                <span class="badge badge-gray">{{ __('messages.calendar_feeds.inactive') }}</span>
                            @endif
                        </div>
                        @if($feed->is_active)
                            <div class="mt-2">
                                <label class="text-xs text-muted">{{ __('messages.calendar_feeds.feed_url') }}</label>
                                <div class="flex items-center gap-2 mt-1" x-data="{ copied: false }">
                                    <input type="text" readonly value="{{ url('/ical/' . $feed->token) }}"
                                        class="form-input text-xs w-full" id="feed-url-{{ $feed->id }}">
                                    <button type="button" class="btn-ghost text-xs shrink-0"
                                        @click="navigator.clipboard.writeText(document.getElementById('feed-url-{{ $feed->id }}').value); copied = true; setTimeout(() => copied = false, 2000)">
                                        <span x-show="!copied">{{ __('messages.calendar_feeds.copy') }}</span>
                                        <span x-show="copied" x-cloak class="text-success">{{ __('messages.calendar_feeds.copied') }}</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-4">
                        <form action="{{ route('calendar-feeds.toggle', $feed) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-ghost text-sm">
                                {{ $feed->is_active ? __('messages.calendar_feeds.deactivate') : __('messages.calendar_feeds.activate') }}
                            </button>
                        </form>
                        @if(!$feed->is_default)
                            <form action="{{ route('calendar-feeds.destroy', $feed) }}" method="POST"
                                onsubmit="return confirm('{{ __('messages.calendar_feeds.delete_confirm') }}')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ghost text-sm text-danger">{{ __('messages.common.delete') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card mb-6">
            <div class="card-body">
                <p class="text-muted">{{ __('messages.calendar_feeds.no_feeds') }}</p>
            </div>
        </div>
    @endforelse

    {{-- Create new feed --}}
    <div class="card">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.calendar_feeds.create') }}</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('calendar-feeds.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="form-label">{{ __('messages.calendar_feeds.feed_name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-input w-full" required>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">{{ __('messages.calendar_feeds.include_teams') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <p class="text-xs text-muted mb-2">{{ __('messages.calendar_feeds.include_teams_desc') }}</p>
                    <div class="space-y-2">
                        @foreach($teams as $team)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="include_teams[]" value="{{ $team->id }}" class="form-checkbox">
                                {{ $team->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="form-label">{{ __('messages.calendar_feeds.include_types') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <p class="text-xs text-muted mb-2">{{ __('messages.calendar_feeds.include_types_desc') }}</p>
                    <div class="flex flex-wrap gap-4">
                        @foreach(['training', 'match', 'competition', 'tournament'] as $type)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="include_event_types[]" value="{{ $type }}" class="form-checkbox">
                                {{ __('messages.events.' . $type) }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn-primary text-sm">{{ __('messages.calendar_feeds.create_feed') }}</button>
            </form>
        </div>
    </div>
@endsection

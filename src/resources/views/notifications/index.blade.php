@extends('layouts.app')

@section('title', __('messages.notifications.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.notifications.title') }}</h1>
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn-ghost text-sm">{{ __('messages.notifications.mark_all_read') }}</button>
        </form>
    </div>

    {{-- Notification preferences --}}
    <div class="card mb-6" x-data="{ open: false }">
        <div class="card-header cursor-pointer flex items-center justify-between" @click="open = !open">
            <h2 class="font-medium">{{ __('messages.notifications.preferences') }}</h2>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-muted transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
        <div class="card-body" x-show="open" x-cloak>
            @php
                $prefs = auth()->user()->notification_preferences ?? [];
            @endphp
            <form action="{{ route('notifications.update-preferences') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="form-label">{{ __('messages.notifications.pref_new_event') }}</label>
                    <select name="preferences[new_event]" class="form-select w-full">
                        <option value="both" @selected(($prefs['new_event'] ?? 'both') === 'both')>{{ __('messages.notifications.channel_both') }}</option>
                        <option value="push" @selected(($prefs['new_event'] ?? '') === 'push')>{{ __('messages.notifications.channel_push') }}</option>
                        <option value="email" @selected(($prefs['new_event'] ?? '') === 'email')>{{ __('messages.notifications.channel_email') }}</option>
                        <option value="none" @selected(($prefs['new_event'] ?? '') === 'none')>{{ __('messages.notifications.channel_none') }}</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">{{ __('messages.notifications.pref_reminder') }}</label>
                    <select name="preferences[event_reminder]" class="form-select w-full">
                        <option value="push" @selected(($prefs['event_reminder'] ?? 'push') === 'push')>{{ __('messages.notifications.channel_push') }}</option>
                        <option value="none" @selected(($prefs['event_reminder'] ?? '') === 'none')>{{ __('messages.notifications.channel_none') }}</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">{{ __('messages.notifications.pref_wall') }}</label>
                    <select name="preferences[wall_posts]" class="form-select w-full">
                        <option value="push" @selected(($prefs['wall_posts'] ?? 'push') === 'push')>{{ __('messages.notifications.channel_push') }}</option>
                        <option value="none" @selected(($prefs['wall_posts'] ?? '') === 'none')>{{ __('messages.notifications.channel_none') }}</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">{{ __('messages.notifications.pref_comments') }}</label>
                    <select name="preferences[comments]" class="form-select w-full">
                        <option value="push" @selected(($prefs['comments'] ?? 'push') === 'push')>{{ __('messages.notifications.channel_push') }}</option>
                        <option value="none" @selected(($prefs['comments'] ?? '') === 'none')>{{ __('messages.notifications.channel_none') }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">{{ __('messages.notifications.silent_from') }}</label>
                        <input type="time" name="preferences[silent_from]" class="form-input w-full" value="{{ $prefs['silent_from'] ?? '22:00' }}">
                    </div>
                    <div>
                        <label class="form-label">{{ __('messages.notifications.silent_to') }}</label>
                        <input type="time" name="preferences[silent_to]" class="form-input w-full" value="{{ $prefs['silent_to'] ?? '07:00' }}">
                    </div>
                </div>

                <p class="text-xs text-muted">{{ __('messages.notifications.critical_note') }}</p>

                <button type="submit" class="btn-primary text-sm">{{ __('messages.common.save') }}</button>
            </form>
        </div>
    </div>

    {{-- Notification list --}}
    @forelse($notifications as $notification)
        <div class="card mb-2 {{ $notification->read_at ? '' : 'border-l-4 border-l-primary' }}">
            <div class="card-body py-3">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm {{ $notification->read_at ? 'text-muted' : 'font-medium' }}">
                            {{ $notification->payload['message'] ?? $notification->type }}
                        </p>
                        <p class="text-xs text-muted mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-4">
                        @if(!$notification->read_at)
                            <form action="{{ route('notifications.mark-read', $notification) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs text-primary hover:underline">{{ __('messages.notifications.mark_read') }}</button>
                            </form>
                        @endif
                        <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-muted hover:text-danger">{{ __('messages.common.delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body">
                <p class="text-muted">{{ __('messages.notifications.no_notifications') }}</p>
            </div>
        </div>
    @endforelse
@endsection

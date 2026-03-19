@extends('layouts.app')

@section('title', __('messages.club_admin.settings'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <x-breadcrumb :items="[
        ['label' => __('messages.club_admin.title'), 'href' => route('club-admin.index')],
        ['label' => __('messages.club_admin.settings')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.club_admin.settings') }}</h1>

    <div class="card max-w-2xl">
        <div class="card-body">
            <form action="{{ route('club-admin.update-settings') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="form-label">{{ __('messages.club_admin.club_name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $club->name) }}"
                        class="form-input @error('name') border-danger @enderror" required>
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="primary_sport" class="form-label">{{ __('messages.club_admin.primary_sport') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="text" name="primary_sport" id="primary_sport" value="{{ old('primary_sport', $club->primary_sport) }}"
                        class="form-input @error('primary_sport') border-danger @enderror">
                    @error('primary_sport')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="address" class="form-label">{{ __('messages.club_admin.club_address') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="text" name="address" id="address" value="{{ old('address', $club->address) }}"
                        class="form-input @error('address') border-danger @enderror">
                    @error('address')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="color" class="form-label">{{ __('messages.club_admin.club_color') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="color" name="color" id="color" value="{{ old('color', $club->color ?? '#1B6B4A') }}"
                        class="h-10 w-16 rounded-lg border border-border cursor-pointer @error('color') border-danger @enderror">
                    @error('color')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bank_account" class="form-label">{{ __('messages.club_admin.bank_account') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="text" name="bank_account" id="bank_account" value="{{ old('bank_account', $club->bank_account) }}"
                        placeholder="CZ6508000000192000145399"
                        class="form-input @error('bank_account') border-danger @enderror">
                    @error('bank_account')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="event_in_progress_minutes" class="form-label">{{ __('messages.club_admin.event_in_progress_minutes') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="event_in_progress_minutes" id="event_in_progress_minutes"
                            value="{{ old('event_in_progress_minutes', $club->settings['event_in_progress_minutes'] ?? 60) }}"
                            min="0" max="1440" step="5"
                            class="form-input w-32 @error('event_in_progress_minutes') border-danger @enderror">
                        <span class="text-sm text-muted">{{ __('messages.club_admin.minutes') }}</span>
                    </div>
                    <p class="text-xs text-muted mt-1">{{ __('messages.club_admin.event_in_progress_hint') }}</p>
                    @error('event_in_progress_minutes')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

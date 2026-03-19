@extends('layouts.auth')

@section('title', __('messages.onboarding.create_club'))

@section('content')
    <div>
        <a href="{{ route('onboarding') }}" class="inline-flex items-center text-text-secondary hover:text-text text-sm mb-4">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            {{ __('messages.common.back') }}
        </a>

        <h1 class="text-xl font-semibold text-text mb-6">{{ __('messages.onboarding.create_club') }}</h1>

        <form method="POST" action="{{ route('onboarding.create-club') }}">
            @csrf

            <div class="mb-4">
                <label for="name" class="form-label">{{ __('messages.onboarding.club_name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-input" required autofocus>
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="primary_sport" class="form-label">{{ __('messages.onboarding.primary_sport') }}</label>
                <select id="primary_sport" name="primary_sport" class="form-select" required>
                    <option value="">--</option>
                    <option value="football" {{ old('primary_sport') === 'football' ? 'selected' : '' }}>{{ __('messages.onboarding.sport_football') }}</option>
                    <option value="hockey" {{ old('primary_sport') === 'hockey' ? 'selected' : '' }}>{{ __('messages.onboarding.sport_hockey') }}</option>
                    <option value="basketball" {{ old('primary_sport') === 'basketball' ? 'selected' : '' }}>{{ __('messages.onboarding.sport_basketball') }}</option>
                    <option value="volleyball" {{ old('primary_sport') === 'volleyball' ? 'selected' : '' }}>{{ __('messages.onboarding.sport_volleyball') }}</option>
                    <option value="handball" {{ old('primary_sport') === 'handball' ? 'selected' : '' }}>{{ __('messages.onboarding.sport_handball') }}</option>
                    <option value="floorball" {{ old('primary_sport') === 'floorball' ? 'selected' : '' }}>{{ __('messages.onboarding.sport_floorball') }}</option>
                    <option value="tennis" {{ old('primary_sport') === 'tennis' ? 'selected' : '' }}>{{ __('messages.onboarding.sport_tennis') }}</option>
                    <option value="athletics" {{ old('primary_sport') === 'athletics' ? 'selected' : '' }}>{{ __('messages.onboarding.sport_athletics') }}</option>
                    <option value="swimming" {{ old('primary_sport') === 'swimming' ? 'selected' : '' }}>{{ __('messages.onboarding.sport_swimming') }}</option>
                    <option value="other" {{ old('primary_sport') === 'other' ? 'selected' : '' }}>{{ __('messages.onboarding.sport_other') }}</option>
                </select>
                @error('primary_sport')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary w-full">{{ __('messages.onboarding.create_button') }}</button>
        </form>
    </div>
@endsection

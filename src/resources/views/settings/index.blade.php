@extends('layouts.app')

@section('title', __('messages.settings.title'))

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.settings.title') }}</h1>
    </div>

    @if(session('success'))
        <div class="alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Language section -->
    <div class="card max-w-2xl">
        <div class="card-header">
            <h3 class="font-medium">{{ __('messages.settings.language') }}</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-4">{{ __('messages.settings.language_desc') }}</p>

            <form method="POST" action="{{ route('settings.locale') }}">
                @csrf
                @method('PUT')

                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="locale" value="cs" {{ $user->locale === 'cs' ? 'checked' : '' }} class="form-radio">
                        <span>{{ __('messages.settings.czech') }}</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="locale" value="en" {{ $user->locale === 'en' ? 'checked' : '' }} class="form-radio">
                        <span>{{ __('messages.settings.english') }}</span>
                    </label>
                </div>

                @error('locale')
                    <p class="form-error mt-2">{{ $message }}</p>
                @enderror

                <div class="mt-6">
                    <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

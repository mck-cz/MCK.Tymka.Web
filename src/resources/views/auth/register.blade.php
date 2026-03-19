@extends('layouts.auth')

@section('title', __('messages.auth.register_title'))

@section('content')
    <h2 class="text-lg font-semibold text-center mb-6">{{ __('messages.auth.register_title') }}</h2>

    @if ($errors->any())
        <div class="alert-error mb-4">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="first_name" class="form-label">{{ __('messages.auth.first_name') }}</label>
                <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    value="{{ old('first_name') }}"
                    required
                    autofocus
                    class="form-input"
                >
            </div>

            <div>
                <label for="last_name" class="form-label">{{ __('messages.auth.last_name') }}</label>
                <input
                    type="text"
                    id="last_name"
                    name="last_name"
                    value="{{ old('last_name') }}"
                    required
                    class="form-input"
                >
            </div>
        </div>

        <div>
            <label for="email" class="form-label">{{ __('messages.auth.email') }}</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                class="form-input"
            >
        </div>

        <div>
            <label for="phone" class="form-label">{{ __('messages.auth.phone') }} <span class="text-muted font-normal">({{ __('messages.common.optional') }})</span></label>
            <input
                type="tel"
                id="phone"
                name="phone"
                value="{{ old('phone') }}"
                class="form-input"
            >
        </div>

        <div>
            <label for="password" class="form-label">{{ __('messages.auth.password') }}</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="form-input"
            >
        </div>

        <div>
            <label for="password_confirmation" class="form-label">{{ __('messages.auth.password_confirm') }}</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
                class="form-input"
            >
        </div>

        <button type="submit" class="btn-primary w-full">
            {{ __('messages.auth.register_button') }}
        </button>
    </form>

    <div class="mt-4 text-center text-sm text-muted">
        {{ __('messages.auth.already_registered') }}
        <a href="{{ route('login') }}" class="text-primary hover:underline">
            {{ __('messages.auth.login_button') }}
        </a>
    </div>
@endsection

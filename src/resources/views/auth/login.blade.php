@extends('layouts.auth')

@section('title', __('messages.auth.login_title'))

@section('content')
    <h2 class="text-lg font-semibold text-center mb-6">{{ __('messages.auth.login_title') }}</h2>

    @if ($errors->any())
        <div class="alert-error mb-4">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="form-label">{{ __('messages.auth.email') }}</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
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

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} class="form-checkbox">
                <span class="text-sm">{{ __('messages.auth.remember_me') }}</span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full">
            {{ __('messages.auth.login_button') }}
        </button>
    </form>

    <div class="mt-4 text-center space-y-2">
        <a href="{{ route('magic-link') }}" class="text-sm text-primary hover:underline">
            {{ __('messages.auth.magic_link') }}
        </a>
    </div>

    <div class="mt-4 text-center text-sm text-muted">
        {{ __('messages.auth.no_account') }}
        <a href="{{ route('register') }}" class="text-primary hover:underline">
            {{ __('messages.auth.register_button') }}
        </a>
    </div>
@endsection

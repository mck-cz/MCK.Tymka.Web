@extends('layouts.auth')

@section('title', __('messages.auth.magic_link_title'))

@section('content')
    <h2 class="text-lg font-semibold text-center mb-2">{{ __('messages.auth.magic_link_title') }}</h2>
    <p class="text-sm text-muted text-center mb-6">{{ __('messages.auth.magic_link_desc') }}</p>

    @if (session('status'))
        <div class="alert-success mb-4">
            <p>{{ session('status') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-error mb-4">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('magic-link') }}" class="space-y-4">
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

        <button type="submit" class="btn-primary w-full">
            {{ __('messages.auth.magic_link_button') }}
        </button>
    </form>

    <div class="mt-4 text-center text-sm text-muted">
        <a href="{{ route('login') }}" class="text-primary hover:underline">
            {{ __('messages.auth.login_button') }}
        </a>
    </div>
@endsection

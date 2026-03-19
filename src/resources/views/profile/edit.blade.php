@extends('layouts.app')

@section('title', __('messages.profile.title'))

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.profile.title') }}</h1>
    </div>

    @if(session('success'))
        <div class="alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card max-w-2xl">
        <div class="card-body">
            <!-- Avatar -->
            <div class="flex flex-col items-center mb-6">
                @if($user->avatar_path)
                    <img src="{{ Storage::url($user->avatar_path) }}" alt="{{ $user->full_name }}"
                        class="w-16 h-16 rounded-full object-cover">
                @else
                    <div class="w-16 h-16 rounded-full bg-primary text-white flex items-center justify-center text-xl font-semibold">
                        {{ $user->initials }}
                    </div>
                @endif
                <p class="mt-2 font-medium">{{ $user->full_name }}</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- First name -->
                    <div>
                        <label for="first_name" class="form-label">{{ __('messages.auth.first_name') }}</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" class="form-input w-full" required>
                        @error('first_name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last name -->
                    <div>
                        <label for="last_name" class="form-label">{{ __('messages.auth.last_name') }}</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="form-input w-full" required>
                        @error('last_name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Nickname -->
                <div class="mt-4">
                    <label for="nickname" class="form-label">{{ __('messages.profile.nickname') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="text" id="nickname" name="nickname" value="{{ old('nickname', $user->nickname) }}" class="form-input w-full">
                    @error('nickname')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mt-4">
                    <label for="email" class="form-label">{{ __('messages.auth.email') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-input w-full" required>
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div class="mt-4">
                    <label for="phone" class="form-label">{{ __('messages.auth.phone') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input w-full">
                    @error('phone')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Birth date -->
                <div class="mt-4">
                    <label for="birth_date" class="form-label">{{ __('messages.profile.birth_date') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}" class="form-input w-full">
                    @error('birth_date')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="mt-4">
                    <label for="address" class="form-label">{{ __('messages.profile.address') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}" class="form-input w-full">
                    @error('address')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Avatar -->
                <div class="mt-4">
                    <label for="avatar" class="form-label">{{ __('messages.profile.avatar') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="file" id="avatar" name="avatar" accept="image/*"
                        class="form-input file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-light file:text-primary">
                    @error('avatar')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Save button -->
                <div class="mt-6">
                    <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

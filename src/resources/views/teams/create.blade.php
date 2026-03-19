@extends('layouts.app')

@section('title', __('messages.teams.create_title'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.teams.title'), 'href' => route('teams.index')],
        ['label' => __('messages.teams.create_title')],
    ]" />

    <h1 class="text-2xl font-semibold mb-6">{{ __('messages.teams.create_title') }}</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('teams.store') }}" method="POST" class="space-y-4">
                @csrf

                {{-- Name --}}
                <div>
                    <label for="name" class="form-label">{{ __('messages.teams.name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="form-input @error('name') border-danger @enderror" required>
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Sport --}}
                <div>
                    <label for="sport" class="form-label">
                        {{ __('messages.teams.sport') }}
                        <span class="text-muted text-xs">({{ __('messages.common.optional') }})</span>
                    </label>
                    <input type="text" name="sport" id="sport" value="{{ old('sport') }}"
                        class="form-input @error('sport') border-danger @enderror">
                    @error('sport')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Age category --}}
                <div>
                    <label for="age_category" class="form-label">
                        {{ __('messages.teams.age_category') }}
                        <span class="text-muted text-xs">({{ __('messages.common.optional') }})</span>
                    </label>
                    <input type="text" name="age_category" id="age_category" value="{{ old('age_category') }}"
                        class="form-input @error('age_category') border-danger @enderror">
                    @error('age_category')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Color --}}
                <div>
                    <label for="color" class="form-label">
                        {{ __('messages.teams.color') }}
                        <span class="text-muted text-xs">({{ __('messages.common.optional') }})</span>
                    </label>
                    <input type="color" name="color" id="color" value="{{ old('color', '#1B6B4A') }}"
                        class="h-10 w-16 rounded-lg border border-border cursor-pointer @error('color') border-danger @enderror">
                    @error('color')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Season --}}
                <div>
                    <label for="season_id" class="form-label">
                        {{ __('messages.teams.season') }}
                        <span class="text-muted text-xs">({{ __('messages.common.optional') }})</span>
                    </label>
                    <select name="season_id" id="season_id"
                        class="form-select @error('season_id') border-danger @enderror">
                        <option value="">{{ __('messages.teams.no_season') }}</option>
                        @foreach($seasons as $season)
                            <option value="{{ $season->id }}" @selected(old('season_id') === $season->id)>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('season_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary">{{ __('messages.teams.create_title') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

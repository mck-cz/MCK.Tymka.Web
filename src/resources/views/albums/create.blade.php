@extends('layouts.app')

@section('title', __('messages.albums.create'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.albums.title'), 'href' => route('albums.index')],
        ['label' => __('messages.albums.create')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.albums.create') }}</h1>

    <div class="card max-w-lg">
        <div class="card-body">
            <form method="POST" action="{{ route('albums.store') }}">
                @csrf

                <div class="mb-4">
                    <label for="title" class="form-label">{{ __('messages.albums.album_title') }}</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" class="form-input w-full" required>
                    @error('title')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="team_id" class="form-label">{{ __('messages.events.team') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <select name="team_id" id="team_id" class="form-input w-full">
                        <option value="">—</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('team_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
                    <a href="{{ route('albums.index') }}" class="btn-secondary">{{ __('messages.common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

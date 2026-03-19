@extends('layouts.app')

@section('title', __('messages.seasons.create'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.seasons.title'), 'href' => route('seasons.index')],
        ['label' => __('messages.seasons.create')],
    ]" />

    <h1 class="text-2xl font-semibold mb-6">{{ __('messages.seasons.create') }}</h1>

    <div class="card max-w-lg">
        <div class="card-body">
            <form action="{{ route('seasons.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="form-label">{{ __('messages.seasons.name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="form-input @error('name') border-danger @enderror" required placeholder="2025/2026">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="start_date" class="form-label">{{ __('messages.seasons.start_date') }}</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}"
                        class="form-input @error('start_date') border-danger @enderror" required>
                    @error('start_date')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="form-label">{{ __('messages.seasons.end_date') }}</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                        class="form-input @error('end_date') border-danger @enderror" required>
                    @error('end_date')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="submit" class="btn-primary">{{ __('messages.seasons.create') }}</button>
                    <a href="{{ route('seasons.index') }}" class="btn-secondary">{{ __('messages.common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

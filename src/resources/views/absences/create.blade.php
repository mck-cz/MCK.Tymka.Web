@extends('layouts.app')

@section('title', __('messages.absences.create'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.absences.title'), 'href' => route('absences.index')],
        ['label' => __('messages.absences.create')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.absences.create') }}</h1>

    <div class="card max-w-2xl">
        <div class="card-body">
            <form action="{{ route('absences.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="reason_type" class="form-label">{{ __('messages.absences.reason') }}</label>
                    <select name="reason_type" id="reason_type" class="form-select w-full @error('reason_type') border-danger @enderror" required>
                        <option value="vacation" @selected(old('reason_type') === 'vacation')>{{ __('messages.absences.reason_vacation') }}</option>
                        <option value="illness" @selected(old('reason_type') === 'illness')>{{ __('messages.absences.reason_illness') }}</option>
                        <option value="injury" @selected(old('reason_type') === 'injury')>{{ __('messages.absences.reason_injury') }}</option>
                        <option value="personal" @selected(old('reason_type') === 'personal')>{{ __('messages.absences.reason_personal') }}</option>
                        <option value="other" @selected(old('reason_type') === 'other')>{{ __('messages.absences.reason_other') }}</option>
                    </select>
                    @error('reason_type')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="starts_at" class="form-label">{{ __('messages.events.start') }}</label>
                        <input type="date" name="starts_at" id="starts_at" value="{{ old('starts_at') }}"
                            class="form-input w-full @error('starts_at') border-danger @enderror" required>
                        @error('starts_at')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="ends_at" class="form-label">{{ __('messages.events.end') }}</label>
                        <input type="date" name="ends_at" id="ends_at" value="{{ old('ends_at') }}"
                            class="form-input w-full @error('ends_at') border-danger @enderror" required>
                        @error('ends_at')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="reason_note" class="form-label">{{ __('messages.absences.note') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <textarea name="reason_note" id="reason_note" rows="2"
                        class="form-input w-full @error('reason_note') border-danger @enderror">{{ old('reason_note') }}</textarea>
                    @error('reason_note')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                @if($teams->isNotEmpty())
                    <div>
                        <label class="form-label">{{ __('messages.absences.apply_to_teams') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                        <p class="text-xs text-muted mb-2">{{ __('messages.absences.apply_to_teams_desc') }}</p>
                        <div class="space-y-2">
                            @foreach($teams as $team)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="apply_to_teams[]" value="{{ $team->id }}"
                                        class="rounded border-border text-primary focus:ring-primary"
                                        @checked(is_array(old('apply_to_teams')) && in_array($team->id, old('apply_to_teams')))>
                                    <span class="text-sm">{{ $team->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
                    <a href="{{ route('absences.index') }}" class="btn-secondary">{{ __('messages.common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

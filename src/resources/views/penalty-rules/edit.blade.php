@extends('layouts.app')

@section('title', __('messages.penalties.edit'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.penalties.title'), 'href' => route('penalty-rules.index')],
        ['label' => __('messages.penalties.edit')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.penalties.edit') }}</h1>

    <div class="card max-w-2xl">
        <div class="card-body">
            <form action="{{ route('penalty-rules.update', $penaltyRule) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="form-label">{{ __('messages.penalties.rule_name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $penaltyRule->name) }}" class="form-input w-full" required>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="team_id" class="form-label">{{ __('messages.events.team') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <select name="team_id" id="team_id" class="form-select w-full">
                        <option value="">{{ __('messages.venue_costs.all_teams') }}</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id', $penaltyRule->team_id) == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('team_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="trigger_type" class="form-label">{{ __('messages.penalties.trigger') }}</label>
                    <select name="trigger_type" id="trigger_type" class="form-select w-full" required>
                        <option value="no_show" @selected(old('trigger_type', $penaltyRule->trigger_type) === 'no_show')>{{ __('messages.penalties.trigger_no_show') }}</option>
                        <option value="late_cancel" @selected(old('trigger_type', $penaltyRule->trigger_type) === 'late_cancel')>{{ __('messages.penalties.trigger_late_cancel') }}</option>
                        <option value="no_response" @selected(old('trigger_type', $penaltyRule->trigger_type) === 'no_response')>{{ __('messages.penalties.trigger_no_response') }}</option>
                    </select>
                    @error('trigger_type') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="penalty_type" class="form-label">{{ __('messages.penalties.penalty_type') }}</label>
                    <select name="penalty_type" id="penalty_type" class="form-select w-full" required>
                        <option value="count_as_attended" @selected(old('penalty_type', $penaltyRule->penalty_type) === 'count_as_attended')>{{ __('messages.penalties.type_count_as_attended') }}</option>
                        <option value="fixed_amount" @selected(old('penalty_type', $penaltyRule->penalty_type) === 'fixed_amount')>{{ __('messages.penalties.type_fixed_amount') }}</option>
                        <option value="percentage_surcharge" @selected(old('penalty_type', $penaltyRule->penalty_type) === 'percentage_surcharge')>{{ __('messages.penalties.type_percentage_surcharge') }}</option>
                    </select>
                    @error('penalty_type') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="amount" class="form-label">{{ __('messages.penalties.amount') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount', $penaltyRule->amount) }}" class="form-input w-full" min="0" step="1">
                    @error('amount') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="late_cancel_hours" class="form-label">{{ __('messages.penalties.late_cancel_hours') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="number" name="late_cancel_hours" id="late_cancel_hours" value="{{ old('late_cancel_hours', $penaltyRule->late_cancel_hours ?? 4) }}" class="form-input w-full" min="1" max="72">
                    @error('late_cancel_hours') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="grace_count" class="form-label">{{ __('messages.penalties.grace_count') }}</label>
                    <input type="number" name="grace_count" id="grace_count" value="{{ old('grace_count', $penaltyRule->grace_count) }}" class="form-input w-full" min="0" max="10">
                    @error('grace_count') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
                    <a href="{{ route('penalty-rules.index') }}" class="btn-secondary">{{ __('messages.common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

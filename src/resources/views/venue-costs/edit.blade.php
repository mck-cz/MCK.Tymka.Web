@extends('layouts.app')

@section('title', __('messages.venue_costs.edit'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.venue_costs.title'), 'href' => route('venue-costs.index')],
        ['label' => __('messages.venue_costs.edit')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.venue_costs.edit') }}</h1>

    @php
        $currentTypes = is_string($venueCost->include_event_types)
            ? json_decode($venueCost->include_event_types, true)
            : ($venueCost->include_event_types ?? ['training']);
    @endphp

    <div class="card max-w-2xl">
        <div class="card-body">
            <form action="{{ route('venue-costs.update', $venueCost) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="form-label">{{ __('messages.venue_costs.name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $venueCost->name) }}" class="form-input w-full" required>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="team_id" class="form-label">{{ __('messages.events.team') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <select name="team_id" id="team_id" class="form-select w-full">
                        <option value="">{{ __('messages.venue_costs.all_teams') }}</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id', $venueCost->team_id) == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('team_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="cost_per_event" class="form-label">{{ __('messages.venue_costs.cost_per_event') }}</label>
                        <input type="number" name="cost_per_event" id="cost_per_event" value="{{ old('cost_per_event', $venueCost->cost_per_event) }}" class="form-input w-full" min="0" step="1" required>
                        @error('cost_per_event') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="currency" class="form-label">{{ __('messages.venue_costs.currency') }}</label>
                        <select name="currency" id="currency" class="form-select w-full" required>
                            <option value="CZK" @selected(old('currency', $venueCost->currency) === 'CZK')>CZK</option>
                            <option value="EUR" @selected(old('currency', $venueCost->currency) === 'EUR')>EUR</option>
                        </select>
                        @error('currency') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="split_method" class="form-label">{{ __('messages.venue_costs.split_method') }}</label>
                    <select name="split_method" id="split_method" class="form-select w-full" required>
                        <option value="per_attendance" @selected(old('split_method', $venueCost->split_method) === 'per_attendance')>{{ __('messages.venue_costs.split_per_attendance') }}</option>
                        <option value="equal_monthly" @selected(old('split_method', $venueCost->split_method) === 'equal_monthly')>{{ __('messages.venue_costs.split_equal_monthly') }}</option>
                        <option value="fixed_per_member" @selected(old('split_method', $venueCost->split_method) === 'fixed_per_member')>{{ __('messages.venue_costs.split_fixed_per_member') }}</option>
                    </select>
                    @error('split_method') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="billing_period" class="form-label">{{ __('messages.venue_costs.billing_period') }}</label>
                    <select name="billing_period" id="billing_period" class="form-select w-full" required>
                        <option value="monthly" @selected(old('billing_period', $venueCost->billing_period) === 'monthly')>{{ __('messages.venue_costs.period_monthly') }}</option>
                        <option value="seasonal" @selected(old('billing_period', $venueCost->billing_period) === 'seasonal')>{{ __('messages.venue_costs.period_seasonal') }}</option>
                        <option value="per_event" @selected(old('billing_period', $venueCost->billing_period) === 'per_event')>{{ __('messages.venue_costs.period_per_event') }}</option>
                    </select>
                    @error('billing_period') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">{{ __('messages.venue_costs.include_event_types') }}</label>
                    <div class="flex flex-wrap gap-4 mt-1">
                        @foreach(['training', 'match', 'competition', 'tournament'] as $type)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="include_event_types[]" value="{{ $type }}"
                                    class="form-checkbox"
                                    @checked(in_array($type, old('include_event_types', $currentTypes)))>
                                {{ __('messages.events.' . $type) }}
                            </label>
                        @endforeach
                    </div>
                    @error('include_event_types') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="bank_account" class="form-label">{{ __('messages.venue_costs.bank_account') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="text" name="bank_account" id="bank_account" value="{{ old('bank_account', $venueCost->bank_account) }}" class="form-input w-full">
                    @error('bank_account') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
                    <a href="{{ route('venue-costs.index') }}" class="btn-secondary">{{ __('messages.common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

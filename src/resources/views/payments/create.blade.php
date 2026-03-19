@extends('layouts.app')

@section('title', __('messages.payments.create'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.payments.title'), 'href' => route('payments.index')],
        ['label' => __('messages.payments.create')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.payments.create') }}</h1>

    <div class="card max-w-2xl">
        <div class="card-body">
            <form action="{{ route('payments.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="form-label">{{ __('messages.payments.name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="form-input @error('name') border-danger @enderror" required>
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="form-label">{{ __('messages.payments.description') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <textarea name="description" id="description" rows="2"
                        class="form-input w-full @error('description') border-danger @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="amount" class="form-label">{{ __('messages.payments.amount') }} (CZK)</label>
                        <input type="number" name="amount" id="amount" value="{{ old('amount') }}"
                            min="1" step="1"
                            class="form-input @error('amount') border-danger @enderror" required>
                        @error('amount')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="due_date" class="form-label">{{ __('messages.payments.due_date') }}</label>
                        <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}"
                            class="form-input @error('due_date') border-danger @enderror" required>
                        @error('due_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="payment_type" class="form-label">{{ __('messages.payments.payment_type') }}</label>
                    <select name="payment_type" id="payment_type" class="form-select @error('payment_type') border-danger @enderror" required>
                        <option value="membership_fee" @selected(old('payment_type') === 'membership_fee')>{{ __('messages.payments.type_membership') }}</option>
                        <option value="event_fee" @selected(old('payment_type') === 'event_fee')>{{ __('messages.payments.type_event') }}</option>
                        <option value="equipment" @selected(old('payment_type') === 'equipment')>{{ __('messages.payments.type_equipment') }}</option>
                        <option value="other" @selected(old('payment_type') === 'other')>{{ __('messages.payments.type_other') }}</option>
                    </select>
                    @error('payment_type')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="team_id" class="form-label">{{ __('messages.events.team') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <select name="team_id" id="team_id" class="form-select @error('team_id') border-danger @enderror">
                        <option value="">{{ __('messages.payments.all_members') }}</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id') === $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('team_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bank_account" class="form-label">{{ __('messages.payments.bank_account') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="text" name="bank_account" id="bank_account" value="{{ old('bank_account') }}"
                        placeholder="CZ6508000000192000145399"
                        class="form-input @error('bank_account') border-danger @enderror">
                    @error('bank_account')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="variable_symbol_prefix" class="form-label">{{ __('messages.payments.vs_prefix') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="text" name="variable_symbol_prefix" id="variable_symbol_prefix" value="{{ old('variable_symbol_prefix') }}"
                        maxlength="6" placeholder="202601"
                        class="form-input @error('variable_symbol_prefix') border-danger @enderror">
                    @error('variable_symbol_prefix')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">{{ __('messages.payments.create_button') }}</button>
                    <a href="{{ route('payments.index') }}" class="btn-secondary">{{ __('messages.common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

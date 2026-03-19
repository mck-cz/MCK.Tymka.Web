@extends('layouts.app')

@section('title', __('messages.payments.edit'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.payments.title'), 'href' => route('payments.index')],
        ['label' => $paymentRequest->name, 'href' => route('payments.show', $paymentRequest)],
        ['label' => __('messages.payments.edit')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.payments.edit') }}</h1>

    <div class="card max-w-2xl">
        <div class="card-body">
            <form action="{{ route('payments.update', $paymentRequest) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="form-label">{{ __('messages.payments.name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $paymentRequest->name) }}"
                        class="form-input @error('name') border-danger @enderror" required>
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="form-label">{{ __('messages.payments.description') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <textarea name="description" id="description" rows="2"
                        class="form-input w-full @error('description') border-danger @enderror">{{ old('description', $paymentRequest->description) }}</textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="amount" class="form-label">{{ __('messages.payments.amount') }} (CZK)</label>
                        <input type="number" name="amount" id="amount" value="{{ old('amount', $paymentRequest->amount) }}"
                            min="1" step="1"
                            class="form-input @error('amount') border-danger @enderror" required>
                        @error('amount')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="due_date" class="form-label">{{ __('messages.payments.due_date') }}</label>
                        <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $paymentRequest->due_date->format('Y-m-d')) }}"
                            class="form-input @error('due_date') border-danger @enderror" required>
                        @error('due_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="bank_account" class="form-label">{{ __('messages.payments.bank_account') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="text" name="bank_account" id="bank_account" value="{{ old('bank_account', $paymentRequest->bank_account) }}"
                        placeholder="CZ6508000000192000145399"
                        class="form-input @error('bank_account') border-danger @enderror">
                    @error('bank_account')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
                    <a href="{{ route('payments.show', $paymentRequest) }}" class="btn-secondary">{{ __('messages.common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

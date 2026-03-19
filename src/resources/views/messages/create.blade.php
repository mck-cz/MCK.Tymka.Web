@extends('layouts.app')

@section('title', __('messages.messages.new_message'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.messages.title'), 'href' => route('messages.index')],
        ['label' => __('messages.messages.new_message')],
    ]" />

    <h1 class="text-2xl font-semibold mb-6">{{ __('messages.messages.new_message') }}</h1>

    <div class="card max-w-lg">
        <div class="card-body">
            <form action="{{ route('messages.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="recipient_id" class="form-label">{{ __('messages.messages.recipient') }}</label>
                    <select name="recipient_id" id="recipient_id"
                        class="form-select @error('recipient_id') border-danger @enderror" required>
                        <option value="">{{ __('messages.messages.select_recipient') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('recipient_id') === $user->id)>
                                {{ $user->full_name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('recipient_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="body" class="form-label">{{ __('messages.messages.message_text') }}</label>
                    <textarea name="body" id="body" rows="4"
                        class="form-input @error('body') border-danger @enderror" required>{{ old('body') }}</textarea>
                    @error('body')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="submit" class="btn-primary">{{ __('messages.messages.send') }}</button>
                    <a href="{{ route('messages.index') }}" class="btn-secondary">{{ __('messages.common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

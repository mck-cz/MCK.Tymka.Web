@extends('layouts.app')

@section('title', $otherParticipant?->user?->full_name ?? __('messages.messages.title'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.messages.title'), 'href' => route('messages.index')],
        ['label' => $otherParticipant?->user?->full_name ?? __('messages.messages.unknown_user')],
    ]" />

    <div class="mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary-light text-primary flex items-center justify-center text-sm font-medium shrink-0">
                @if($otherParticipant?->user)
                    {{ strtoupper(mb_substr($otherParticipant->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($otherParticipant->user->last_name, 0, 1)) }}
                @else
                    ?
                @endif
            </div>
            <h1 class="text-xl font-semibold">{{ $otherParticipant?->user?->full_name ?? __('messages.messages.unknown_user') }}</h1>
        </div>
    </div>

    {{-- Messages --}}
    <div class="card mb-4">
        <div class="card-body space-y-4 max-h-[60vh] overflow-y-auto" id="messages-container">
            @forelse($messages as $msg)
                <div class="flex {{ $msg->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%] rounded-xl px-4 py-2 {{ $msg->sender_id === auth()->id() ? 'bg-primary text-white' : 'bg-bg text-text' }}">
                        <p class="text-sm whitespace-pre-wrap">{{ $msg->body }}</p>
                        <p class="text-xs mt-1 {{ $msg->sender_id === auth()->id() ? 'text-white/60' : 'text-muted' }}">
                            {{ app()->getLocale() === 'cs' ? $msg->created_at->format('d.m. H:i') : $msg->created_at->format('M d, H:i') }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center">{{ __('messages.messages.no_messages') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Reply form --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('messages.reply', $conversation) }}" method="POST" class="flex gap-3">
                @csrf
                <div class="flex-1">
                    <textarea name="body" rows="2" class="form-input w-full" placeholder="{{ __('messages.messages.type_message') }}" required></textarea>
                    @error('body')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="shrink-0">
                    <button type="submit" class="btn-primary h-full">{{ __('messages.messages.send') }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    </script>
@endsection

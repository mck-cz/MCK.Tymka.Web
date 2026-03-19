@extends('layouts.app')

@section('title', __('messages.messages.title'))

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold">{{ __('messages.messages.title') }}</h1>
        <a href="{{ route('messages.create') }}" class="btn-primary">{{ __('messages.messages.new_message') }}</a>
    </div>

    @if($conversations->isEmpty())
        <div class="card">
            <div class="card-body">
                <p class="text-muted">{{ __('messages.messages.no_conversations') }}</p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                @foreach($conversations as $conversation)
                    @php
                        $otherParticipant = $conversation->conversationParticipants->firstWhere('user_id', '!=', auth()->id());
                        $lastMessage = $conversation->messages->first();
                        $myParticipant = $conversation->conversationParticipants->firstWhere('user_id', auth()->id());
                        $isUnread = $lastMessage && $myParticipant && (
                            !$myParticipant->last_read_at || $myParticipant->last_read_at->lt($lastMessage->created_at)
                        ) && $lastMessage->sender_id !== auth()->id();
                    @endphp
                    <a href="{{ route('messages.show', $conversation) }}"
                        class="flex items-center gap-3 px-6 py-4 hover:bg-bg transition-colors border-b border-border last:border-0 {{ $isUnread ? 'bg-primary-light/30' : '' }}">
                        <div class="w-10 h-10 rounded-full bg-primary-light text-primary flex items-center justify-center text-sm font-medium shrink-0">
                            @if($otherParticipant?->user)
                                {{ strtoupper(mb_substr($otherParticipant->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($otherParticipant->user->last_name, 0, 1)) }}
                            @else
                                ?
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-medium {{ $isUnread ? 'text-text' : '' }}">
                                    {{ $otherParticipant?->user?->full_name ?? __('messages.messages.unknown_user') }}
                                </span>
                                @if($lastMessage)
                                    <span class="text-xs text-muted shrink-0 ml-2">
                                        {{ app()->getLocale() === 'cs' ? $lastMessage->created_at->format('d.m. H:i') : $lastMessage->created_at->format('M d, H:i') }}
                                    </span>
                                @endif
                            </div>
                            @if($lastMessage)
                                <p class="text-sm text-muted truncate">
                                    @if($lastMessage->sender_id === auth()->id())
                                        {{ __('messages.messages.you') }}:
                                    @endif
                                    {{ $lastMessage->body }}
                                </p>
                            @endif
                        </div>
                        @if($isUnread)
                            <div class="w-2.5 h-2.5 rounded-full bg-primary shrink-0"></div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endsection

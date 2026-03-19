@extends('layouts.app')

@section('title', ($teamPost->title ?? __('messages.wall.title')) . ' — ' . $team->name)

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <x-breadcrumb :items="[
        ['label' => __('messages.teams.title'), 'href' => route('teams.index')],
        ['label' => $team->name, 'href' => route('teams.show', $team)],
        ['label' => __('messages.wall.title'), 'href' => route('teams.wall', $team)],
        ['label' => $teamPost->title ?? __('messages.wall.message')],
    ]" />

    <div class="card mb-6">
        <div class="card-body">
            {{-- Author + meta --}}
            <div class="flex gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-primary-light text-primary flex items-center justify-center text-sm font-medium shrink-0">
                    {{ strtoupper(mb_substr($teamPost->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($teamPost->user->last_name, 0, 1)) }}
                </div>
                <div>
                    <span class="font-medium">{{ $teamPost->user->full_name }}</span>
                    <p class="text-xs text-muted">
                        {{ app()->getLocale() === 'cs' ? $teamPost->created_at->format('d.m.Y H:i') : $teamPost->created_at->format('Y-m-d H:i') }}
                    </p>
                </div>
            </div>

            {{-- Article title --}}
            @if($teamPost->title)
                <h1 class="text-2xl font-semibold mb-4">{{ $teamPost->title }}</h1>
            @endif

            {{-- Full article body --}}
            <div class="trix-render text-sm leading-relaxed mb-6">{!! $teamPost->body !!}</div>

            {{-- Attachments (non-image files) --}}
            @if($teamPost->attachments->where('mime_type', 'not like', 'image/%')->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-border">
                    @foreach($teamPost->attachments->reject(fn ($a) => str_starts_with($a->mime_type, 'image/')) as $attachment)
                        <a href="{{ Storage::disk('public')->url($attachment->file_path) }}" target="_blank"
                            class="flex items-center gap-2 text-sm text-primary hover:underline mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ $attachment->original_name }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Poll options --}}
            @if($teamPost->post_type === 'poll' && $teamPost->pollOptions->isNotEmpty())
                @php
                    $totalVotes = $teamPost->pollOptions->sum(fn ($o) => $o->pollVotes->count());
                    $userVotedOption = $teamPost->pollOptions->first(fn ($o) => $o->pollVotes->contains('user_id', auth()->id()));
                @endphp
                <div class="space-y-2 mb-3">
                    @foreach($teamPost->pollOptions->sortBy('sort_order') as $option)
                        @php
                            $voteCount = $option->pollVotes->count();
                            $pct = $totalVotes > 0 ? round($voteCount / $totalVotes * 100) : 0;
                            $isVoted = $userVotedOption && $userVotedOption->id === $option->id;
                        @endphp
                        <div class="relative">
                            <div class="absolute inset-0 bg-primary-light rounded-lg" style="width: {{ $pct }}%"></div>
                            <div class="relative flex items-center justify-between px-3 py-2">
                                <form action="{{ route('poll-votes.store', $option) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <button type="submit" class="text-sm {{ $isVoted ? 'font-semibold text-primary' : '' }}">
                                        {{ $option->label }}
                                    </button>
                                </form>
                                <span class="text-xs text-muted">{{ $voteCount }} ({{ $pct }}%)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-muted">{{ __('messages.wall.total_votes') }}: {{ $totalVotes }}</p>
            @endif
        </div>
    </div>

    {{-- Comments section --}}
    <div class="card">
        <div class="card-body">
            @if($teamPost->teamPostComments->isNotEmpty())
                <div class="space-y-4 mb-4">
                    @foreach($teamPost->teamPostComments->sortBy('created_at') as $comment)
                        <div class="flex gap-3">
                            <div class="w-7 h-7 rounded-full bg-primary-light text-primary flex items-center justify-center text-[10px] font-medium shrink-0">
                                {{ strtoupper(mb_substr($comment->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($comment->user->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium">{{ $comment->user->full_name }}</span>
                                    <span class="text-xs text-muted">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm mt-0.5">{{ $comment->body }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-muted mb-4">{{ __('messages.comments.no_comments') }}</p>
            @endif

            {{-- Add comment --}}
            @if($canInteract ?? false)
                <div class="pt-3 border-t border-border">
                    <form action="{{ route('team-post-comments.store', $teamPost) }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="body" placeholder="{{ __('messages.comments.placeholder') }}"
                            class="form-input w-full text-sm" required>
                        <button type="submit" class="btn-ghost text-sm shrink-0">{{ __('messages.messages.send') }}</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('teams.wall', $team) }}" class="text-sm text-muted hover:text-primary">
            ← {{ __('messages.wall.back_to_wall') }}
        </a>
    </div>
@endsection

@push('styles')
<style>
.trix-render {
    line-height: 1.7;
}
.trix-render strong {
    font-weight: 600;
}
.trix-render em {
    font-style: italic;
}
.trix-render a {
    color: var(--color-primary);
    text-decoration: underline;
}
.trix-render ul, .trix-render ol {
    padding-left: 1.5rem;
    margin: 0.5rem 0;
}
.trix-render ul {
    list-style: disc;
}
.trix-render ol {
    list-style: decimal;
}
.trix-render blockquote {
    border-left: 3px solid var(--color-border);
    padding-left: 0.75rem;
    color: var(--color-text-secondary);
    margin: 0.5rem 0;
}
.trix-render pre {
    background: var(--color-bg);
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.85em;
    overflow-x: auto;
    margin: 0.5rem 0;
}
.trix-render figure {
    margin: 0.75rem 0;
}
.trix-render figure img {
    max-width: 100%;
    border-radius: 8px;
}
.trix-render figcaption {
    font-size: 0.8em;
    color: var(--color-text-secondary);
    text-align: center;
    margin-top: 0.25rem;
}
</style>
@endpush

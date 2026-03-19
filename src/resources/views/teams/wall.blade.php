@extends('layouts.app')

@section('title', $team->name . ' — ' . __('messages.wall.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <x-breadcrumb :items="[
        ['label' => __('messages.teams.title'), 'href' => route('teams.index')],
        ['label' => $team->name, 'href' => route('teams.show', $team)],
        ['label' => __('messages.wall.title')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ $team->name }} — {{ __('messages.wall.title') }}</h1>

    {{-- New Post Form (admin + coaches only) --}}
    @if($canPost ?? false)
    <div class="card mb-6" x-data="wallPostForm()">
        <div class="card-body">
            <form action="{{ route('team-posts.store', $team) }}" method="POST" class="space-y-3" @submit="beforeSubmit">
                @csrf
                <input type="hidden" name="post_type" :value="postType">

                <div class="flex gap-2 mb-2">
                    <button type="button" @click="postType = 'message'"
                        :class="postType === 'message' ? 'btn-primary' : 'btn-secondary'" class="text-sm">
                        {{ __('messages.wall.message') }}
                    </button>
                    <button type="button" @click="postType = 'poll'"
                        :class="postType === 'poll' ? 'btn-primary' : 'btn-secondary'" class="text-sm">
                        {{ __('messages.wall.poll') }}
                    </button>
                </div>

                {{-- Trix editor for message posts --}}
                <div x-show="postType === 'message'" x-cloak>
                    <input id="trix-body" type="hidden" name="body" x-ref="trixInput">
                    <trix-editor input="trix-body" class="trix-content form-input !p-0" placeholder="{{ __('messages.wall.write_message') }}"></trix-editor>
                </div>

                {{-- Plain textarea for poll question --}}
                <div x-show="postType === 'poll'" x-cloak>
                    <textarea name="poll_body" rows="2" class="form-input w-full"
                        placeholder="{{ __('messages.wall.poll_question') }}"
                        x-ref="pollBody"></textarea>
                </div>

                {{-- Hidden inputs for attachment IDs --}}
                <template x-for="id in attachmentIds" :key="id">
                    <input type="hidden" name="attachment_ids[]" :value="id">
                </template>

                <template x-if="postType === 'poll'">
                    <div class="space-y-2">
                        <template x-for="i in optionCount" :key="i">
                            <input type="text" :name="'poll_options[' + (i-1) + ']'" class="form-input w-full text-sm"
                                :placeholder="'{{ __('messages.wall.option') }} ' + i" required>
                        </template>
                        <button type="button" @click="optionCount = Math.min(optionCount + 1, 10)" class="text-sm text-primary hover:underline">
                            + {{ __('messages.wall.add_option') }}
                        </button>
                    </div>
                </template>

                <button type="submit" class="btn-primary text-sm">{{ __('messages.wall.post') }}</button>
            </form>
        </div>
    </div>
    @endif

    {{-- Posts --}}
    @forelse($posts as $post)
        <div class="card mb-4">
            <div class="card-body">
                <div class="flex gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-xs font-medium shrink-0">
                        {{ strtoupper(mb_substr($post->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($post->user->last_name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-sm">{{ $post->user->full_name }}</span>
                            <span class="text-xs text-muted">{{ $post->created_at->diffForHumans() }}</span>
                            @if($post->user_id === auth()->id())
                                <form action="{{ route('team-posts.destroy', $post) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-muted hover:text-danger">{{ __('messages.common.delete') }}</button>
                                </form>
                            @endif
                        </div>
                        <div class="trix-render text-sm mt-1">{!! $post->body !!}</div>
                    </div>
                </div>

                {{-- Poll options --}}
                @if($post->post_type === 'poll' && $post->pollOptions->isNotEmpty())
                    @php
                        $totalVotes = $post->pollOptions->sum(fn ($o) => $o->pollVotes->count());
                        $userVotedOption = $post->pollOptions->first(fn ($o) => $o->pollVotes->contains('user_id', auth()->id()));
                    @endphp
                    <div class="space-y-2 mb-3">
                        @foreach($post->pollOptions->sortBy('sort_order') as $option)
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

                {{-- Comments --}}
                @if($post->teamPostComments->isNotEmpty())
                    <div class="mt-3 pt-3 border-t border-border space-y-2">
                        @foreach($post->teamPostComments->sortBy('created_at') as $comment)
                            <div class="flex gap-2">
                                <div class="w-6 h-6 rounded-full bg-primary-light text-primary flex items-center justify-center text-[10px] font-medium shrink-0">
                                    {{ strtoupper(mb_substr($comment->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($comment->user->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="text-xs font-medium">{{ $comment->user->full_name }}</span>
                                    <span class="text-xs text-muted ml-1">{{ $comment->created_at->diffForHumans() }}</span>
                                    <p class="text-sm">{{ $comment->body }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Add comment --}}
                @if($isDirectMember ?? false)
                <div class="mt-3 pt-3 border-t border-border">
                    <form action="{{ route('team-post-comments.store', $post) }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="body" placeholder="{{ __('messages.comments.placeholder') }}"
                            class="form-input w-full text-sm" required>
                        <button type="submit" class="btn-ghost text-sm shrink-0">{{ __('messages.messages.send') }}</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body">
                <p class="text-muted">{{ __('messages.wall.no_posts') }}</p>
            </div>
        </div>
    @endforelse
@endsection

@push('scripts')
<script>
function wallPostForm() {
    return {
        postType: 'message',
        optionCount: 2,
        attachmentIds: [],

        init() {
            const editor = this.$el.querySelector('trix-editor');
            if (!editor) return;

            // Handle file attachments via Trix
            editor.addEventListener('trix-attachment-add', (event) => {
                const attachment = event.attachment;
                if (attachment.file) {
                    this.uploadAttachment(attachment);
                }
            });
        },

        async uploadAttachment(attachment) {
            const formData = new FormData();
            formData.append('file', attachment.file);

            try {
                const response = await fetch('{{ route("team-posts.upload") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (!response.ok) throw new Error('Upload failed');

                const data = await response.json();
                this.attachmentIds.push(data.id);
                attachment.setAttributes({
                    url: data.url,
                    href: data.href,
                });
            } catch (error) {
                console.error('Upload error:', error);
                attachment.remove();
            }
        },

        beforeSubmit() {
            // For polls, copy the poll textarea value to the body field
            if (this.postType === 'poll') {
                this.$refs.trixInput.value = this.$refs.pollBody.value;
            }
        }
    };
}
</script>
@endpush

@push('styles')
<style>
/* Trix editor styling */
trix-editor {
    min-height: 120px;
    border: none !important;
    padding: 0.5rem 0.75rem;
    outline: none;
}
trix-editor:focus {
    outline: none;
    box-shadow: none;
}
trix-toolbar {
    padding: 0.5rem;
    border-bottom: 1px solid var(--color-border);
}
trix-toolbar .trix-button-group {
    border: 1px solid var(--color-border);
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 0;
}
trix-toolbar .trix-button {
    border: none;
    border-bottom: none !important;
    padding: 0.35rem 0.5rem;
    background: var(--color-surface);
}
trix-toolbar .trix-button:hover {
    background: var(--color-primary-light);
}
trix-toolbar .trix-button.trix-active {
    background: var(--color-primary-light);
    color: var(--color-primary);
}
trix-toolbar .trix-button-group + .trix-button-group {
    margin-left: 0.5rem;
}
/* Hide heading button (not needed for wall posts) */
trix-toolbar .trix-button-group--block-tools .trix-button[data-trix-attribute="heading1"] {
    display: none;
}

/* Rendered Trix content */
.trix-render {
    line-height: 1.6;
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
    margin: 0.25rem 0;
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
    background: var(--color-gray-light);
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.85em;
    overflow-x: auto;
    margin: 0.5rem 0;
}
.trix-render figure {
    margin: 0.5rem 0;
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

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

                {{-- Title --}}
                <div>
                    <input type="text" name="title" class="form-input w-full font-semibold"
                        placeholder="{{ __('messages.wall.post_title_placeholder') }}" required
                        x-ref="titleInput">
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

    {{-- Posts as article previews --}}
    @forelse($posts as $post)
        <div class="card mb-4">
            <div class="card-body">
                {{-- Author + meta --}}
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
                    </div>
                </div>

                {{-- Article title --}}
                @if($post->title)
                    <h2 class="text-lg font-semibold mb-2">
                        <a href="{{ route('team-posts.show', $post) }}" class="hover:text-primary transition-colors">
                            {{ $post->title }}
                        </a>
                    </h2>
                @endif

                {{-- Excerpt (stripped text, max 200 chars) --}}
                @if($post->post_type === 'message')
                    @php
                        $plainText = strip_tags($post->body);
                        $excerpt = mb_strlen($plainText) > 200 ? mb_substr($plainText, 0, 200) . '…' : $plainText;
                    @endphp
                    <p class="text-sm text-text-secondary mb-3">{{ $excerpt }}</p>

                    @if(mb_strlen($plainText) > 200 || str_contains($post->body, '<img') || str_contains($post->body, '<figure'))
                        <a href="{{ route('team-posts.show', $post) }}" class="text-sm text-primary font-medium hover:underline">
                            {{ __('messages.wall.read_more') }} →
                        </a>
                    @endif
                @endif

                {{-- Poll options (inline, no need for detail page) --}}
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

                {{-- Comment count --}}
                @if($post->teamPostComments->isNotEmpty())
                    <div class="mt-3 pt-3 border-t border-border">
                        <a href="{{ route('team-posts.show', $post) }}" class="text-sm text-muted hover:text-primary">
                            {{ $post->teamPostComments->count() }} {{ __('messages.wall.comment_posted') }}
                        </a>
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

            // Enable image resizing after attachment is inserted
            editor.addEventListener('trix-attachment-add', (event) => {
                setTimeout(() => this.enableImageResize(editor), 100);
            });
        },

        enableImageResize(editor) {
            const figures = editor.element.querySelectorAll('figure[data-trix-attachment]');
            figures.forEach(figure => {
                if (figure.dataset.resizable) return;
                figure.dataset.resizable = 'true';

                const img = figure.querySelector('img');
                if (!img) return;

                // Create resize handle
                const handle = document.createElement('div');
                handle.className = 'image-resize-handle';
                figure.style.position = 'relative';
                figure.style.display = 'inline-block';
                figure.appendChild(handle);

                let startX, startWidth;

                handle.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    startX = e.clientX;
                    startWidth = img.offsetWidth;

                    const onMouseMove = (e) => {
                        const newWidth = Math.max(100, startWidth + (e.clientX - startX));
                        img.style.width = newWidth + 'px';
                        img.style.height = 'auto';
                    };

                    const onMouseUp = () => {
                        document.removeEventListener('mousemove', onMouseMove);
                        document.removeEventListener('mouseup', onMouseUp);
                    };

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });
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
    min-height: 200px;
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

/* Image resize handle */
figure[data-trix-attachment] {
    position: relative;
    display: inline-block;
}
figure[data-trix-attachment]:hover .image-resize-handle {
    opacity: 1;
}
.image-resize-handle {
    position: absolute;
    right: -4px;
    bottom: -4px;
    width: 16px;
    height: 16px;
    background: var(--color-primary);
    border: 2px solid var(--color-surface);
    border-radius: 2px;
    cursor: se-resize;
    opacity: 0;
    transition: opacity 0.15s;
    z-index: 10;
}

/* Rendered content */
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
    background: var(--color-bg);
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

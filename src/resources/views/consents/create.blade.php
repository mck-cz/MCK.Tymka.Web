@extends('layouts.app')

@section('title', __('messages.consents.create_type'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.consents.title'), 'href' => route('consents.index')],
        ['label' => __('messages.consents.create_type')],
    ]" />

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.consents.create_type') }}</h1>

    <form action="{{ route('consent-types.store') }}" method="POST" class="card max-w-3xl">
        @csrf
        <div class="card-body space-y-4">
            <div>
                <label for="name" class="form-label">{{ __('messages.consents.type_name') }} *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="form-input @error('name') border-danger @enderror" required>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="form-label">{{ __('messages.consents.description') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                <input type="text" name="description" id="description" value="{{ old('description') }}"
                    class="form-input" placeholder="{{ __('messages.consents.description_hint') }}">
            </div>

            <div>
                <label class="form-label">{{ __('messages.consents.content') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                <p class="text-xs text-muted mb-2">{{ __('messages.consents.content_hint') }}</p>
                <div x-data="richEditor('{{ old('content') ? 'old' : '' }}')" x-init="initEditor()">
                    <div x-ref="editor" class="bg-surface rounded-b-lg" style="min-height: 200px;">{!! old('content', '') !!}</div>
                    <input type="hidden" name="content" x-ref="contentInput" value="{{ old('content', '') }}">
                </div>
                @error('content') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_required" value="0">
                <input type="checkbox" name="is_required" id="is_required" value="1"
                    {{ old('is_required') ? 'checked' : '' }} class="form-checkbox">
                <label for="is_required" class="text-sm">{{ __('messages.consents.is_required') }}</label>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary">{{ __('messages.common.save') }}</button>
            </div>
        </div>
    </form>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('richEditor', (hasOld) => ({
        quill: null,
        initEditor() {
            this.quill = new Quill(this.$refs.editor, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        ['link'],
                        ['clean']
                    ]
                }
            });

            // Sync content to hidden input on text change
            this.quill.on('text-change', () => {
                const html = this.quill.root.innerHTML;
                this.$refs.contentInput.value = html === '<p><br></p>' ? '' : html;
            });

            // Set initial value from hidden input if we have old() data
            const initial = this.$refs.contentInput.value;
            if (initial) {
                this.quill.root.innerHTML = initial;
            }
        }
    }));
});
</script>
@endpush

@extends('layouts.app')

@section('title', $album->title)

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <x-breadcrumb :items="[
        ['label' => __('messages.albums.title'), 'href' => route('albums.index')],
        ['label' => $album->title],
    ]" />

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold">{{ $album->title }}</h1>
            <div class="text-sm text-muted mt-1">
                @if($album->team)
                    {{ $album->team->name }} &middot;
                @endif
                {{ $album->createdBy?->full_name }} &middot;
                {{ app()->getLocale() === 'cs' ? $album->created_at->format('d.m.Y') : $album->created_at->format('Y-m-d') }}
            </div>
        </div>
        @if($album->created_by === auth()->id())
            <form action="{{ route('albums.destroy', $album) }}" method="POST"
                onsubmit="return confirm('{{ __('messages.albums.delete_confirm') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger text-sm">{{ __('messages.common.delete') }}</button>
            </form>
        @endif
    </div>

    {{-- Upload form --}}
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.albums.upload_photo') }}</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('albums.upload-photo', $album) }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label for="photo" class="form-label">{{ __('messages.albums.photo') }}</label>
                    <input type="file" name="photo" id="photo" accept="image/*" class="form-input w-full" required>
                    @error('photo')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="caption" class="form-label">{{ __('messages.albums.caption') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                    <input type="text" name="caption" id="caption" class="form-input w-full" maxlength="255">
                    @error('caption')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn-primary shrink-0">{{ __('messages.albums.upload') }}</button>
            </form>
        </div>
    </div>

    {{-- Photos grid --}}
    @if($album->photos->isEmpty())
        <div class="card">
            <div class="card-body text-center text-muted py-8">
                {{ __('messages.albums.no_photos') }}
            </div>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($album->photos as $photo)
                <div class="card overflow-hidden">
                    <img src="{{ asset('storage/' . $photo->file_path) }}" alt="{{ $photo->caption }}"
                        class="w-full h-40 object-cover">
                    <div class="p-3">
                        @if($photo->caption)
                            <p class="text-sm mb-1">{{ $photo->caption }}</p>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted">{{ $photo->uploadedBy?->full_name }}</span>
                            @if($photo->uploaded_by === auth()->id())
                                <form action="{{ route('albums.delete-photo', $photo) }}" method="POST"
                                    onsubmit="return confirm('{{ __('messages.albums.delete_photo_confirm') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-muted hover:text-danger">{{ __('messages.common.delete') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

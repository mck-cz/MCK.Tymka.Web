@extends('layouts.app')

@section('title', __('messages.albums.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.albums.title') }}</h1>
        <a href="{{ route('albums.create') }}" class="btn-primary">{{ __('messages.albums.create') }}</a>
    </div>

    @if($albums->isEmpty())
        <div class="card">
            <div class="card-body text-center text-muted py-8">
                {{ __('messages.albums.no_albums') }}
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($albums as $album)
                <a href="{{ route('albums.show', $album) }}" class="card hover:border-primary transition-colors">
                    <div class="card-body">
                        <h3 class="font-medium mb-1">{{ $album->title }}</h3>
                        <div class="flex items-center gap-2 text-sm text-muted mb-2">
                            @if($album->team)
                                <span>{{ $album->team->name }}</span>
                                <span>&middot;</span>
                            @endif
                            <span>{{ $album->photos_count }} {{ __('messages.albums.photos_count') }}</span>
                        </div>
                        <div class="text-xs text-muted">
                            {{ $album->createdBy?->full_name }} &middot;
                            {{ app()->getLocale() === 'cs' ? $album->created_at->format('d.m.Y') : $album->created_at->format('Y-m-d') }}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection

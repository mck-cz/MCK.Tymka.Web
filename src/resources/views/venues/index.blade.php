@extends('layouts.app')

@section('title', __('messages.venues.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold">{{ __('messages.venues.title') }}</h1>
        @if($isClubAdmin || $isCoachInClub)
            <a href="{{ route('venues.create') }}" class="btn-primary">{{ __('messages.venues.create') }}</a>
        @endif
    </div>

    @if($venues->isEmpty())
        <div class="card">
            <div class="card-body">
                <p class="text-muted">{{ __('messages.venues.no_venues') }}</p>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($venues as $venue)
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-medium text-base">{{ $venue->name }}</h3>
                            <div class="flex items-center gap-1 shrink-0 ml-2">
                                @if($venue->is_favorite)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-accent" fill="currentColor" viewBox="0 0 24 24" aria-label="{{ __('messages.venues.is_favorite') }}">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        @if($venue->address)
                            <p class="text-sm text-muted mb-2">{{ $venue->address }}</p>
                        @endif
                        <div class="flex flex-wrap gap-2 mb-3">
                            @if($venue->sport_type)
                                <span class="badge badge-primary">{{ $venue->sport_type }}</span>
                            @endif
                            @if($venue->latitude && $venue->longitude)
                                <span class="badge badge-gray">GPS</span>
                            @endif
                        </div>
                        @if($venue->latitude && $venue->longitude)
                            <div class="rounded-lg overflow-hidden border border-border mb-3" style="height: 150px;">
                                <div id="map-{{ $venue->id }}" class="w-full h-full"></div>
                            </div>
                        @endif
                        @if($venue->notes)
                            <p class="text-sm text-muted mb-3">{{ $venue->notes }}</p>
                        @endif
                        @if($isClubAdmin || $isCoachInClub)
                            <div class="flex gap-2">
                                <a href="{{ route('venues.edit', $venue) }}" class="btn-ghost text-sm">{{ __('messages.common.edit') }}</a>
                                @if($isClubAdmin)
                                    <form action="{{ route('venues.destroy', $venue) }}" method="POST"
                                        onsubmit="return confirm('{{ __('messages.venues.delete_confirm') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost text-danger text-sm">{{ __('messages.common.delete') }}</button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($venues->where('latitude', '!=', null)->isNotEmpty())
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @foreach($venues as $venue)
                    @if($venue->latitude && $venue->longitude)
                        (function() {
                            var map = L.map('map-{{ $venue->id }}', {
                                zoomControl: false,
                                dragging: false,
                                scrollWheelZoom: false,
                                doubleClickZoom: false,
                                touchZoom: false,
                            }).setView([{{ $venue->latitude }}, {{ $venue->longitude }}], 15);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OSM'
                            }).addTo(map);
                            L.marker([{{ $venue->latitude }}, {{ $venue->longitude }}]).addTo(map);
                        })();
                    @endif
                @endforeach
            });
        </script>
    @endif
@endsection

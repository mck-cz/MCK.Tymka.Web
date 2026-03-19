@extends('layouts.app')

@section('title', __('messages.venues.create'))

@section('content')
    <x-breadcrumb :items="[
        ['label' => __('messages.venues.title'), 'href' => route('venues.index')],
        ['label' => __('messages.venues.create')],
    ]" />

    <h1 class="text-2xl font-semibold mb-6">{{ __('messages.venues.create') }}</h1>

    <div class="card max-w-2xl">
        <div class="card-body">
            <form action="{{ route('venues.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="form-label">{{ __('messages.venues.name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="form-input @error('name') border-danger @enderror" required>
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="address" class="form-label">
                        {{ __('messages.venues.address') }}
                        <span class="text-muted text-xs">({{ __('messages.common.optional') }})</span>
                    </label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}"
                        class="form-input @error('address') border-danger @enderror">
                    @error('address')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sport_type" class="form-label">
                        {{ __('messages.venues.sport_type') }}
                        <span class="text-muted text-xs">({{ __('messages.common.optional') }})</span>
                    </label>
                    <input type="text" name="sport_type" id="sport_type" value="{{ old('sport_type') }}"
                        class="form-input @error('sport_type') border-danger @enderror">
                    @error('sport_type')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="form-label">
                        {{ __('messages.venues.notes') }}
                        <span class="text-muted text-xs">({{ __('messages.common.optional') }})</span>
                    </label>
                    <textarea name="notes" id="notes" rows="3"
                        class="form-input @error('notes') border-danger @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_favorite" value="1" {{ old('is_favorite') ? 'checked' : '' }} class="form-checkbox">
                        <span class="text-sm">{{ __('messages.venues.is_favorite') }}</span>
                    </label>
                </div>

                {{-- Map for location picking --}}
                <div>
                    <label class="form-label">{{ __('messages.venues.location') }}
                        <span class="text-muted text-xs">({{ __('messages.venues.click_map') }})</span>
                    </label>
                    <div id="map-picker" class="rounded-lg border border-border" style="height: 300px;"></div>
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                    <p class="text-xs text-muted mt-1" id="coords-display">
                        @if(old('latitude') && old('longitude'))
                            {{ old('latitude') }}, {{ old('longitude') }}
                        @endif
                    </p>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="submit" class="btn-primary">{{ __('messages.venues.create') }}</button>
                    <a href="{{ route('venues.index') }}" class="btn-secondary">{{ __('messages.common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var lat = document.getElementById('latitude').value || 49.75;
            var lng = document.getElementById('longitude').value || 15.75;
            var zoom = (document.getElementById('latitude').value) ? 15 : 7;

            var map = L.map('map-picker').setView([lat, lng], zoom);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OSM'
            }).addTo(map);

            var marker = null;
            if (document.getElementById('latitude').value) {
                marker = L.marker([lat, lng]).addTo(map);
            }

            map.on('click', function(e) {
                document.getElementById('latitude').value = e.latlng.lat.toFixed(7);
                document.getElementById('longitude').value = e.latlng.lng.toFixed(7);
                document.getElementById('coords-display').textContent = e.latlng.lat.toFixed(7) + ', ' + e.latlng.lng.toFixed(7);

                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(map);
                }
            });
        });
    </script>
@endsection

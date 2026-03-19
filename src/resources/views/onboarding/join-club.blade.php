@extends('layouts.auth')

@section('title', __('messages.onboarding.join_club'))

@section('content')
    <div x-data="clubSearch()">
        <a href="{{ route('onboarding') }}" class="inline-flex items-center text-text-secondary hover:text-text text-sm mb-4">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            {{ __('messages.common.back') }}
        </a>

        <h1 class="text-xl font-semibold text-text mb-6">{{ __('messages.onboarding.join_club') }}</h1>

        @if (session('success'))
            <div class="alert-success mb-4">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert-error mb-4">{{ session('error') }}</div>
        @endif

        <div class="mb-4">
            <label for="search" class="form-label">{{ __('messages.common.search') }}</label>
            <input
                type="text"
                id="search"
                class="form-input"
                placeholder="{{ __('messages.onboarding.search_placeholder') }}"
                x-model="query"
                @input.debounce.300ms="search()"
            >
        </div>

        {{-- Loading --}}
        <div x-show="loading" class="text-text-secondary text-sm py-2">
            {{ __('messages.common.loading') }}
        </div>

        {{-- No results --}}
        <div x-show="!loading && searched && results.length === 0" class="text-text-secondary text-sm py-2">
            {{ __('messages.onboarding.no_results') }}
        </div>

        {{-- Results --}}
        <div x-show="results.length > 0" class="space-y-2">
            <template x-for="club in results" :key="club.id">
                <div class="card">
                    <div class="card-body flex items-center justify-between">
                        <div>
                            <p class="font-medium text-text" x-text="club.name"></p>
                            <p class="text-text-secondary text-sm" x-text="sportLabel(club.primary_sport)"></p>
                        </div>
                        <button
                            type="button"
                            class="btn-secondary text-sm"
                            @click="selectClub(club)"
                            x-show="selectedClub?.id !== club.id"
                        >
                            {{ __('messages.onboarding.request_join') }}
                        </button>
                    </div>

                    {{-- Join request form --}}
                    <div x-show="selectedClub?.id === club.id" class="px-4 pb-4">
                        <form method="POST" action="{{ route('onboarding.join-club') }}">
                            @csrf
                            <input type="hidden" name="club_id" :value="club.id">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.onboarding.message') }}</label>
                                <textarea name="message" class="form-input" rows="2"></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="btn-primary text-sm">{{ __('messages.onboarding.request_join') }}</button>
                                <button type="button" class="btn-secondary text-sm" @click="selectedClub = null">{{ __('messages.common.cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        function clubSearch() {
            const sportLabels = @json([
                'football' => __('messages.onboarding.sport_football'),
                'hockey' => __('messages.onboarding.sport_hockey'),
                'basketball' => __('messages.onboarding.sport_basketball'),
                'volleyball' => __('messages.onboarding.sport_volleyball'),
                'handball' => __('messages.onboarding.sport_handball'),
                'floorball' => __('messages.onboarding.sport_floorball'),
                'tennis' => __('messages.onboarding.sport_tennis'),
                'athletics' => __('messages.onboarding.sport_athletics'),
                'swimming' => __('messages.onboarding.sport_swimming'),
                'other' => __('messages.onboarding.sport_other'),
            ]);

            return {
                query: '',
                results: [],
                loading: false,
                searched: false,
                selectedClub: null,

                sportLabel(sport) {
                    return sportLabels[sport] || sport;
                },

                async search() {
                    if (this.query.length < 2) {
                        this.results = [];
                        this.searched = false;
                        return;
                    }

                    this.loading = true;
                    this.selectedClub = null;

                    try {
                        const response = await fetch(`{{ route('onboarding.search-clubs') }}?q=${encodeURIComponent(this.query)}`);
                        this.results = await response.json();
                    } catch (e) {
                        this.results = [];
                    }

                    this.loading = false;
                    this.searched = true;
                },

                selectClub(club) {
                    this.selectedClub = club;
                },
            };
        }
    </script>
@endsection

@extends('layouts.auth')

@section('title', __('messages.onboarding.title'))

@section('content')
    <div x-data="{ step: 1, role: '{{ old('user_role', '') }}' }">
        <h1 class="text-xl font-semibold text-text text-center mb-1">{{ __('messages.onboarding.title') }}</h1>
        <p class="text-text-secondary text-center mb-6">{{ __('messages.onboarding.subtitle') }}</p>

        @if (session('success'))
            <div class="alert-success mb-4">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert-error mb-4">{{ session('error') }}</div>
        @endif

        {{-- Progress indicator --}}
        <div class="flex items-center justify-center gap-2 mb-6">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-colors"
                :class="step >= 1 ? 'bg-primary text-white' : 'bg-border text-muted'">1</div>
            <div class="w-8 h-0.5 transition-colors" :class="step >= 2 ? 'bg-primary' : 'bg-border'"></div>
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-colors"
                :class="step >= 2 ? 'bg-primary text-white' : 'bg-border text-muted'">2</div>
        </div>

        {{-- Step 1: Role selection --}}
        <div x-show="step === 1" x-transition>
            <p class="text-center text-sm text-muted mb-4">{{ __('messages.onboarding.select_role') }}</p>

            <div class="space-y-3">
                <button @click="role = 'parent'; step = 2"
                    class="card w-full text-left block hover:border-primary transition-colors cursor-pointer">
                    <div class="card-body flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-primary-light flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-text mb-1">{{ __('messages.onboarding.role_parent') }}</h2>
                            <p class="text-text-secondary text-sm">{{ __('messages.onboarding.role_parent_desc') }}</p>
                        </div>
                    </div>
                </button>

                <button @click="role = 'coach'; step = 2"
                    class="card w-full text-left block hover:border-primary transition-colors cursor-pointer">
                    <div class="card-body flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-accent-light flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-text mb-1">{{ __('messages.onboarding.role_coach') }}</h2>
                            <p class="text-text-secondary text-sm">{{ __('messages.onboarding.role_coach_desc') }}</p>
                        </div>
                    </div>
                </button>

                <button @click="role = 'player'; step = 2"
                    class="card w-full text-left block hover:border-primary transition-colors cursor-pointer">
                    <div class="card-body flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-bg flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-text mb-1">{{ __('messages.onboarding.role_player') }}</h2>
                            <p class="text-text-secondary text-sm">{{ __('messages.onboarding.role_player_desc') }}</p>
                        </div>
                    </div>
                </button>
            </div>
        </div>

        {{-- Step 2: Create or join club --}}
        <div x-show="step === 2" x-cloak x-transition>
            <button @click="step = 1" class="inline-flex items-center text-text-secondary hover:text-text text-sm mb-4 cursor-pointer">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('messages.common.back') }}
            </button>

            <div class="space-y-3">
                {{-- Coach/Admin: show "Create club" first --}}
                <template x-if="role === 'coach'">
                    <div class="space-y-3">
                        <a href="{{ route('onboarding.create-club') }}" class="card block hover:border-primary transition-colors">
                            <div class="card-body flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg bg-primary-light flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="font-semibold text-text mb-1">{{ __('messages.onboarding.create_club') }}</h2>
                                    <p class="text-text-secondary text-sm">{{ __('messages.onboarding.create_club_desc') }}</p>
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('onboarding.join-club') }}" class="card block hover:border-primary transition-colors">
                            <div class="card-body flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg bg-accent-light flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="font-semibold text-text mb-1">{{ __('messages.onboarding.join_club') }}</h2>
                                    <p class="text-text-secondary text-sm">{{ __('messages.onboarding.join_club_desc') }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </template>

                {{-- Parent/Player: show "Join club" first --}}
                <template x-if="role !== 'coach'">
                    <div class="space-y-3">
                        <a href="{{ route('onboarding.join-club') }}" class="card block hover:border-primary transition-colors">
                            <div class="card-body flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg bg-accent-light flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="font-semibold text-text mb-1">{{ __('messages.onboarding.join_club') }}</h2>
                                    <p class="text-text-secondary text-sm">{{ __('messages.onboarding.join_club_desc') }}</p>
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('onboarding.create-club') }}" class="card block hover:border-primary transition-colors">
                            <div class="card-body flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg bg-primary-light flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="font-semibold text-text mb-1">{{ __('messages.onboarding.create_club') }}</h2>
                                    <p class="text-text-secondary text-sm">{{ __('messages.onboarding.create_club_desc') }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </template>
            </div>
        </div>

        <div class="mt-6 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-text-secondary hover:text-text text-sm cursor-pointer">
                    {{ __('messages.nav.logout') }}
                </button>
            </form>
        </div>
    </div>
@endsection

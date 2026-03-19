<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Tymko'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-bg text-text text-sm font-sans min-h-screen flex flex-col antialiased">

    <!-- Mobile overlay -->
    <div
        x-data
        x-show="$store.sidebar.mobileOpen"
        x-transition:enter="transition-opacity duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="$store.sidebar.mobileOpen = false"
        class="fixed inset-0 bg-black/50 z-40 lg:hidden"
        style="display: none;"
    ></div>

    <div class="flex flex-1">
        <!-- Sidebar -->
        <aside
            x-data
            :class="[
                $store.sidebar.mobileOpen ? 'translate-x-0' : '-translate-x-full',
                $store.sidebar.expanded ? 'lg:w-60' : 'lg:w-16',
                'lg:translate-x-0'
            ]"
            class="fixed inset-y-0 left-0 z-50 flex flex-col text-white transition-all duration-200 w-60 lg:static lg:z-auto"
            style="background: linear-gradient(180deg, #0F4A32 0%, #0A3622 50%, #071F14 100%);"
        >
            <!-- Logo -->
            <div class="flex items-center h-16 px-4">
                <a href="/" class="flex items-center gap-2.5 overflow-hidden">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);">
                        <span class="text-white font-semibold text-base">T</span>
                    </div>
                    <span
                        x-data
                        :class="$store.sidebar.expanded ? 'lg:opacity-100 lg:w-auto' : 'lg:opacity-0 lg:w-0'"
                        class="font-semibold text-lg whitespace-nowrap transition-all duration-200 overflow-hidden tracking-tight"
                    >Tymko</span>
                </a>
            </div>

            <!-- Club switcher -->
            @php
                $userClubs = Auth::user()->clubMemberships()->with('club')->where('status', 'active')->get();
                $currentClub = $userClubs->firstWhere('club_id', session('current_club_id'));
            @endphp
            @if($userClubs->count() > 1)
                <div x-data="{ clubOpen: false }" class="px-3 mb-3 relative">
                    <button
                        @click="clubOpen = !clubOpen"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm cursor-pointer transition-all duration-150"
                        style="background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.25);"
                        onmouseover="this.style.background='rgba(34, 197, 94, 0.2)'"
                        onmouseout="this.style.background='rgba(34, 197, 94, 0.12)'"
                    >
                        <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.35) 0%, rgba(255, 255, 255, 0.1) 100%);">
                            <svg class="w-3.5 h-3.5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <span
                            x-data
                            :class="$store.sidebar.expanded ? 'lg:opacity-100 lg:w-auto' : 'lg:opacity-0 lg:w-0'"
                            class="truncate flex-1 text-left transition-all duration-200 overflow-hidden text-white font-medium"
                        >{{ $currentClub?->club?->name ?? 'Club' }}</span>
                        <svg
                            x-data
                            :class="[$store.sidebar.expanded ? 'lg:opacity-100' : 'lg:opacity-0', clubOpen ? 'rotate-180' : '']"
                            class="w-3 h-3 shrink-0 transition-all duration-200 text-white/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="clubOpen" @click.away="clubOpen = false" x-cloak
                        class="mt-1 bg-white rounded-lg shadow-lg py-1 text-text text-sm z-50">
                        @foreach($userClubs as $membership)
                            @if($membership->club_id !== session('current_club_id'))
                                <form action="{{ route('club.switch') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="club_id" value="{{ $membership->club_id }}">
                                    <button type="submit" class="w-full text-left px-3 py-2 hover:bg-bg transition-colors cursor-pointer">
                                        {{ $membership->club->name }}
                                    </button>
                                </form>
                            @endif
                        @endforeach
                    </div>
                </div>
            @elseif($currentClub)
                <div x-data class="px-3 mb-3">
                    <div
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm"
                        style="background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.25);"
                    >
                        <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.35) 0%, rgba(255, 255, 255, 0.1) 100%);">
                            <svg class="w-3.5 h-3.5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <span
                            :class="$store.sidebar.expanded ? 'lg:opacity-100 lg:w-auto' : 'lg:opacity-0 lg:w-0'"
                            class="truncate transition-all duration-200 overflow-hidden text-white font-medium"
                        >{{ $currentClub->club->name }}</span>
                    </div>
                </div>
            @endif

            <!-- Toggle button (desktop only) -->
            <button
                x-data
                @click="$store.sidebar.expanded = !$store.sidebar.expanded"
                aria-label="{{ __('messages.nav.toggle_sidebar') }}"
                class="hidden lg:flex items-center justify-center mx-3 mb-2 p-1.5 rounded-lg bg-white/8 border border-white/15 hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-150 cursor-pointer"
            >
                <svg
                    :class="$store.sidebar.expanded ? '' : 'rotate-180'"
                    class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Navigation -->
            <nav class="flex-1 px-3 overflow-y-auto">
                @php
                    $isAdminOrCoach = ($isClubAdmin ?? false) || ($isCoachInClub ?? false);
                @endphp

                {{-- Main navigation --}}
                <div class="space-y-1 mb-2">
                    @php
                        $navItems = [
                            ['key' => 'dashboard', 'href' => '/dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                            ['key' => 'calendar', 'href' => '/calendar', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                            ['key' => 'teams', 'href' => '/teams', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                            ['key' => 'events', 'href' => '/events', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                            ['key' => 'messages', 'href' => '/messages', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                        ];
                    @endphp

                    @foreach($navItems as $item)
                        @php $isActive = request()->is(ltrim($item['href'], '/') . '*') || (request()->is('/') && $item['key'] === 'dashboard'); @endphp
                        <a
                            href="{{ $item['href'] }}"
                            x-data
                            class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-150 relative {{ $isActive ? 'bg-white/15 text-white shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                            @if($isActive) style="box-shadow: inset 3px 0 0 0 #22c55e;" @endif
                        >
                            <svg class="w-5 h-5 shrink-0 {{ $isActive ? 'text-green-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                            </svg>
                            <span
                                :class="$store.sidebar.expanded ? 'lg:opacity-100 lg:w-auto' : 'lg:opacity-0 lg:w-0'"
                                class="whitespace-nowrap transition-all duration-200 overflow-hidden"
                            >{{ __('messages.nav.' . $item['key']) }}</span>
                        </a>
                    @endforeach
                </div>

                {{-- Personal section --}}
                <div class="space-y-1 pt-2 border-t border-white/8">
                    <div x-data :class="$store.sidebar.expanded ? 'lg:opacity-100' : 'lg:opacity-0'" class="px-3 py-1 text-[10px] uppercase tracking-wider text-white/50 transition-all duration-200 overflow-hidden whitespace-nowrap">
                        {{ __('messages.nav.section_personal') }}
                    </div>
                    @php
                        $personalItems = [
                            ['key' => 'notifications', 'href' => '/notifications', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                            ['key' => 'absences', 'href' => '/absences', 'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
                            ['key' => 'calendar_feeds', 'href' => '/calendar-feeds', 'icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'],
                        ];
                    @endphp
                    @foreach($personalItems as $item)
                        @php $isActive = request()->is(ltrim($item['href'], '/') . '*'); @endphp
                        <a
                            href="{{ $item['href'] }}"
                            x-data
                            class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-150 relative {{ $isActive ? 'bg-white/15 text-white shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                            @if($isActive) style="box-shadow: inset 3px 0 0 0 #22c55e;" @endif
                        >
                            <svg class="w-5 h-5 shrink-0 {{ $isActive ? 'text-green-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                            </svg>
                            <span
                                :class="$store.sidebar.expanded ? 'lg:opacity-100 lg:w-auto' : 'lg:opacity-0 lg:w-0'"
                                class="whitespace-nowrap transition-all duration-200 overflow-hidden"
                            >{{ __('messages.nav.' . $item['key']) }}</span>
                        </a>
                    @endforeach
                </div>

                {{-- Club tools (visible to coach + admin) --}}
                @if($isAdminOrCoach)
                    <div class="space-y-1 pt-2 border-t border-white/8">
                        <div x-data :class="$store.sidebar.expanded ? 'lg:opacity-100' : 'lg:opacity-0'" class="px-3 py-1 text-[10px] uppercase tracking-wider text-white/50 transition-all duration-200 overflow-hidden whitespace-nowrap">
                            {{ __('messages.nav.section_club') }}
                        </div>
                        @php
                            $clubItems = [
                                ['key' => 'venues', 'href' => '/venues', 'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
                                ['key' => 'statistics', 'href' => '/statistics', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                                ['key' => 'albums', 'href' => '/albums', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                                ['key' => 'recurrence_rules', 'href' => '/recurrence-rules', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                                ['key' => 'venue_costs', 'href' => '/venue-costs', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                            ];
                        @endphp
                        @foreach($clubItems as $item)
                            @php $isActive = request()->is(ltrim($item['href'], '/') . '*'); @endphp
                            <a
                                href="{{ $item['href'] }}"
                                x-data
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-150 relative {{ $isActive ? 'bg-white/15 text-white shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                                @if($isActive) style="box-shadow: inset 3px 0 0 0 #22c55e;" @endif
                            >
                                <svg class="w-5 h-5 shrink-0 {{ $isActive ? 'text-green-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                </svg>
                                <span
                                    :class="$store.sidebar.expanded ? 'lg:opacity-100 lg:w-auto' : 'lg:opacity-0 lg:w-0'"
                                    class="whitespace-nowrap transition-all duration-200 overflow-hidden"
                                >{{ __('messages.nav.' . $item['key']) }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Administration (owner/admin only — collapsible) --}}
                @if($isClubAdmin)
                    <div class="space-y-1 pt-2 border-t border-white/8" x-data="{ adminOpen: {{ request()->is('club-admin*') || request()->is('seasons*') || request()->is('templates*') || request()->is('payments*') || request()->is('penalty-rules*') || request()->is('consents*') || request()->is('custom-fields*') ? 'true' : 'false' }} }">
                        <button
                            @click="adminOpen = !adminOpen"
                            class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-150 text-white/80 hover:bg-white/10 hover:text-white cursor-pointer"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span
                                x-data
                                :class="$store.sidebar.expanded ? 'lg:opacity-100 lg:w-auto' : 'lg:opacity-0 lg:w-0'"
                                class="flex-1 text-left whitespace-nowrap transition-all duration-200 overflow-hidden"
                            >{{ __('messages.nav.section_admin') }}</span>
                            <svg
                                :class="[adminOpen ? 'rotate-180' : '', $store.sidebar.expanded ? 'lg:opacity-100' : 'lg:opacity-0']"
                                class="w-3 h-3 shrink-0 transition-all duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="adminOpen" x-collapse x-cloak class="space-y-0.5 pl-3">
                            @php
                                $adminItems = [
                                    ['key' => 'club_admin', 'href' => '/club-admin', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                                    ['key' => 'payments', 'href' => '/payments', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                                    ['key' => 'seasons', 'href' => '/seasons', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                    ['key' => 'templates', 'href' => '/templates', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                                    ['key' => 'penalty_rules', 'href' => '/penalty-rules', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                                    ['key' => 'consents', 'href' => '/consents', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                                    ['key' => 'custom_fields', 'href' => '/custom-fields', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                                ];
                            @endphp
                            @foreach($adminItems as $item)
                                @php $isActive = request()->is(ltrim($item['href'], '/') . '*'); @endphp
                                <a
                                    href="{{ $item['href'] }}"
                                    class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-all duration-150 text-sm relative {{ $isActive ? 'bg-white/15 text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}"
                                    @if($isActive) style="box-shadow: inset 3px 0 0 0 #22c55e;" @endif
                                >
                                    <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-green-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                    </svg>
                                    <span
                                        x-data
                                        :class="$store.sidebar.expanded ? 'lg:opacity-100 lg:w-auto' : 'lg:opacity-0 lg:w-0'"
                                        class="whitespace-nowrap transition-all duration-200 overflow-hidden"
                                    >{{ __('messages.nav.' . $item['key']) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </nav>

            <!-- User section -->
            <div class="px-3 py-4 border-t border-white/8">
                <div x-data class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.4) 0%, rgba(255, 255, 255, 0.15) 100%); border: 1px solid rgba(255, 255, 255, 0.2);">
                        <span class="text-xs font-medium">{{ Auth::user()->initials ?? 'U' }}</span>
                    </div>
                    <div
                        :class="$store.sidebar.expanded ? 'lg:opacity-100 lg:w-auto' : 'lg:opacity-0 lg:w-0'"
                        class="overflow-hidden transition-all duration-200 min-w-0"
                    >
                        <div class="text-sm font-medium truncate">{{ Auth::user()->full_name ?? __('messages.nav.profile') }}</div>
                        <div class="text-xs text-white/50 truncate">{{ Auth::user()->email ?? '' }}</div>
                    </div>
                </div>
                <!-- Settings & Logout -->
                <div
                    x-data
                    :class="$store.sidebar.expanded ? 'lg:opacity-100' : 'lg:opacity-0 lg:pointer-events-none'"
                    class="flex gap-1 transition-all duration-200"
                >
                    <a href="/settings" class="flex-1 flex items-center justify-center gap-1 px-2 py-1.5 text-xs text-white/60 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ __('messages.nav.settings') }}
                    </a>
                    <form method="POST" action="/logout" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-1 px-2 py-1.5 text-xs text-white/60 hover:text-white hover:bg-white/10 rounded-lg transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            {{ __('messages.nav.logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top bar (mobile) -->
            <header class="lg:hidden flex items-center h-16 px-4 bg-surface border-b border-border">
                <button
                    x-data
                    @click="$store.sidebar.mobileOpen = true"
                    aria-label="{{ __('messages.nav.open_menu') }}"
                    class="p-2 -ml-2 rounded-lg hover:bg-bg focus:outline-none focus:ring-2 focus:ring-primary/50 transition-colors cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <span class="ml-3 font-semibold text-base">Tymko</span>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 lg:p-6">
                @yield('content')
                {{ $slot ?? '' }}
            </main>
        </div>
    </div>

    @livewireScripts

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', {
                expanded: true,
                mobileOpen: false,

                toggle() {
                    this.expanded = !this.expanded;
                },
            });
        });
    </script>
    @stack('scripts')
</body>
</html>

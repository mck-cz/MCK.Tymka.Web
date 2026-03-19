<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('messages.app_name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-bg text-text text-sm font-sans min-h-screen antialiased">

    <!-- Hero Section -->
    <section class="flex flex-col items-center justify-center px-4 py-20 lg:py-32 text-center">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-lg bg-primary flex items-center justify-center">
                <span class="text-white font-semibold text-2xl">T</span>
            </div>
            <h1 class="text-4xl lg:text-5xl font-semibold text-text">{{ __('messages.app_name') }}</h1>
        </div>
        <p class="text-text-secondary text-base lg:text-lg max-w-md mb-8">
            {{ __('messages.welcome.hero_text') }}
        </p>
        <div class="flex gap-3">
            <a href="/register" class="btn-accent text-base px-6 py-3">{{ __('messages.welcome.get_started') }}</a>
            <a href="/login" class="btn-secondary text-base px-6 py-3">{{ __('messages.welcome.sign_in') }}</a>
        </div>
    </section>

    <!-- Feature Cards -->
    <section class="max-w-4xl mx-auto px-4 pb-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-primary-light flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h4 class="font-medium text-text">{{ __('messages.welcome.features.teams') }}</h4>
                    </div>
                    <p class="text-text-secondary">{{ __('messages.welcome.features.teams_desc') }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-accent-light flex items-center justify-center">
                            <svg class="w-5 h-5 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h4 class="font-medium text-text">{{ __('messages.welcome.features.events') }}</h4>
                    </div>
                    <p class="text-text-secondary">{{ __('messages.welcome.features.events_desc') }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-success-light flex items-center justify-center">
                            <svg class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <h4 class="font-medium text-text">{{ __('messages.welcome.features.payments') }}</h4>
                    </div>
                    <p class="text-text-secondary">{{ __('messages.welcome.features.payments_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    @if(app()->environment('local'))
    <!-- Style Guide (dev only) -->
    <section class="max-w-4xl mx-auto px-4 pb-20">
        <h2 class="text-2xl font-semibold text-text mb-8 text-center">Design System</h2>

        <div class="card mb-6">
            <div class="card-body">
                <h3 class="text-base font-semibold text-text mb-4">Buttons</h3>
                <div class="flex flex-wrap gap-3 items-center">
                    <button class="btn-primary">Primary</button>
                    <button class="btn-secondary">Secondary</button>
                    <button class="btn-accent">Accent</button>
                    <button class="btn-ghost">Ghost</button>
                    <button class="btn-danger">Danger</button>
                    <button class="btn-primary" disabled>Disabled</button>
                </div>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-body">
                <h3 class="text-base font-semibold text-text mb-4">Badges</h3>
                <div class="flex flex-wrap gap-3 items-center">
                    <span class="badge badge-primary">Primary</span>
                    <span class="badge badge-accent">Accent</span>
                    <span class="badge badge-success">Success</span>
                    <span class="badge badge-warning">Warning</span>
                    <span class="badge badge-danger">Danger</span>
                    <span class="badge badge-gray">Gray</span>
                </div>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-body">
                <h3 class="text-base font-semibold text-text mb-4">Form Elements</h3>
                <div class="max-w-sm space-y-4">
                    <div>
                        <label class="form-label">{{ __('messages.auth.email') }}</label>
                        <input type="email" class="form-input" placeholder="you@example.com">
                    </div>
                    <div>
                        <label class="form-label">{{ __('messages.auth.password') }}</label>
                        <input type="password" class="form-input" placeholder="••••••••">
                        <p class="form-error">This field is required.</p>
                    </div>
                    <div>
                        <label class="form-label">Select</label>
                        <select class="form-select">
                            <option>Option 1</option>
                            <option>Option 2</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
            <h3 class="text-base font-semibold text-text mb-4">Color Palette</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                <div><div class="h-12 rounded-lg bg-primary mb-1"></div><span class="text-xs text-text-secondary">Primary</span></div>
                <div><div class="h-12 rounded-lg bg-primary-dark mb-1"></div><span class="text-xs text-text-secondary">Primary Dark</span></div>
                <div><div class="h-12 rounded-lg bg-primary-light border border-border mb-1"></div><span class="text-xs text-text-secondary">Primary Light</span></div>
                <div><div class="h-12 rounded-lg bg-accent mb-1"></div><span class="text-xs text-text-secondary">Accent</span></div>
                <div><div class="h-12 rounded-lg bg-success mb-1"></div><span class="text-xs text-text-secondary">Success</span></div>
                <div><div class="h-12 rounded-lg bg-danger mb-1"></div><span class="text-xs text-text-secondary">Danger</span></div>
            </div>
            </div>
        </div>
    </section>
    @endif

</body>
</html>

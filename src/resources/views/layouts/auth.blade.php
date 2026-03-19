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
</head>
<body class="bg-bg text-text text-sm font-sans min-h-screen flex flex-col items-center justify-center px-4 antialiased">

    <div class="w-full max-w-sm">
        <!-- Logo -->
        <div class="flex justify-center mb-8">
            <a href="/" class="flex items-center gap-2">
                <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center">
                    <span class="text-white font-semibold text-xl">T</span>
                </div>
                <span class="font-semibold text-2xl text-text">Tymko</span>
            </a>
        </div>

        <!-- Card -->
        <div class="card">
            <div class="card-body">
                @yield('content')
                {{ $slot ?? '' }}
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>

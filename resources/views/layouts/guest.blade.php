<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'FinanceApp') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ url('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-gray-900 via-gray-800 to-emerald-900">
        <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-white/95 backdrop-blur-sm shadow-2xl overflow-hidden sm:rounded-2xl">
            <div class="flex justify-center mb-6">
                <a href="/">
                    <x-application-logo class="w-20 h-20" />
                </a>
            </div>

            <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">{{ __('Welcome') }}</h2>
            <p class="text-sm text-center text-gray-500 mb-8">{{ __('Manage your finances with ease') }}</p>

            <div>
                {{ $slot }}
            </div>
        </div>

        <footer class="w-full sm:max-w-md text-center mt-6 text-gray-400 text-xs">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'FinanceApp') }}. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>

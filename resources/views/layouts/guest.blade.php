<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Pti - Auth') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <!-- Background pattern -->
        <div class="absolute inset-0 z-0 opacity-30 dark:opacity-10 overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-100 dark:bg-indigo-900 rounded-full mix-blend-multiply filter blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-purple-100 dark:bg-purple-900 rounded-full mix-blend-multiply filter blur-3xl"></div>
        </div>
        <!-- Main content container -->
        <div class="w-full sm:max-w-5xl mx-auto px-6 py-4 sm:px-6 lg:px-8 z-10 mt-6">
            {{ $slot }}
        </div>

        <!-- Footer -->
        <div class="w-full mt-auto z-10">
            <footer class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.</p>
            </footer>
        </div>
    </div>
</body>
</html>
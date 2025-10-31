<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Panel Administratora</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Assets -->
        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <!-- Fallback for missing built assets: Tailwind + flag-icons via CDN -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.1/css/flag-icons.min.css">
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="font-sans antialiased">
        <div class=" bg-gray-100 dark:bg-gray-900">
            <!-- Navbar z flagami języków -->
            <nav class="flex items-center justify-end gap-2  dark:bg-gray-800 dark:border-gray-700">
                    
                    <x-language-switcher-btn />
            </nav>
            <!-- Sidebar Navigation -->
            <div class="flex">
                <!-- Main Content -->
                <div class="flex-1">    
                    <!-- Main Content -->
                    <main class="flex-1 ">
                        @yield('content') 
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>

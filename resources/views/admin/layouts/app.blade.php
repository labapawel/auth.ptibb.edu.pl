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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <!-- Navbar z flagami języków -->
            <nav class="flex items-center justify-end px-6 py-2 bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                    
                    <div class=" right-0 mt-2 w-36 rounded-md shadow-lg gap-2 bg-white ring-2 ring-black ring-opacity-5 focus:outline-none z-50 dark:bg-gray-800" role="menu" aria-orientation="vertical" aria-labelledby="lang-menu">
                        <div class="py-1 flex" role="none">
                            <a href="{{ url('lang/pl') }}"
                               class="flex items-center gap-2 px-5 py-2 text-base rounded-lg transition-all group
                               {{ app()->getLocale() === 'pl' ? 'bg-indigo-600 text-white dark:bg-indigo-700' : 'text-gray-700 hover:bg-gray-300 dark:text-gray-200 dark:hover:bg-gray-900' }}"
                               role="menuitem">
                                <span class="text-2xl group-hover:scale-125 transition-transform">🇵🇱</span>
                            </a>
                            <a href="{{ url('lang/en') }}"
                               class="flex items-center gap-2 px-5 py-2 text-base rounded-lg transition-all group
                               {{ app()->getLocale() === 'en' ? 'bg-indigo-600 text-white dark:bg-indigo-700' : 'text-gray-700 hover:bg-gray-300 dark:text-gray-200 dark:hover:bg-gray-900' }}"
                               role="menuitem">
                                <span class="text-2xl group-hover:scale-125 transition-transform">🇬🇧</span>
                            </a>
                            <a href="{{ url('lang/uk') }}"
                               class="flex items-center gap-2 px-5 py-2 text-base rounded-lg transition-all group
                               {{ app()->getLocale() === 'uk' ? 'bg-indigo-600 text-white dark:bg-indigo-700' : 'text-gray-700 hover:bg-gray-300 dark:text-gray-200 dark:hover:bg-gray-900' }}"
                               role="menuitem">
                                <span class="text-2xl group-hover:scale-125 transition-transform">🇺🇦</span>
                            </a>
                    </div>
            </nav>
            <!-- Sidebar Navigation -->
            <div class="flex">
                <!-- Main Content -->
                <div class="flex-1">    
                    <!-- Main Content -->
                    <main class="flex-1 pb-8">
                        @yield('content') 
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>

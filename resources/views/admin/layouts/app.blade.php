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
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @php
                $navItems = [
                    ['route' => 'admin.dashboard', 'label' => __('lang.admin.dashboard')],
                    ['route' => 'ldap.users.index', 'label' => __('lang.admin.ldap_users')],
                    ['route' => 'ldap.groups.index', 'label' => __('lang.admin.ldap_groups')],
                    ['route' => 'admin.pc', 'label' => __('lang.admin.pc')],
                ];
            @endphp

            <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex flex-col md:flex-row md:items-center md:gap-6">
                        <a href="{{ route('admin.dashboard') }}" class="text-xl font-semibold text-gray-800 dark:text-white">
                            {{ config('app.name', 'PTI Auth') }}
                        </a>
                        <nav class="flex flex-wrap items-center gap-3 text-sm">
                            @foreach ($navItems as $item)
                                <a
                                    href="{{ route($item['route']) }}"
                                    @class([
                                        'px-3 py-2 rounded-md transition-colors',
                                        'bg-indigo-600 text-white' => request()->routeIs($item['route']),
                                        'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' => !request()->routeIs($item['route']),
                                    ])
                                >
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-language-switcher-btn />
                        <a href="{{ route('admin.logout') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                            {{ __('lang.admin.logout') }}
                        </a>
                    </div>
                </div>
            </header>

            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                @yield('content')
            </main>
        </div>
    </body>
</html>

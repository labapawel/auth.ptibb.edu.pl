<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-100 dark:bg-gray-900">
        <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                @auth
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Welcome, {{ Auth::user()->name }}</h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">You are logged in!</p>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 my-4"></div>
                        @if(Auth::user()->isTaskPermission())
                            <div class="bg-emerald-50 dark:bg-emerald-900/30 p-4 rounded-lg">
                                <h2 class="text-lg font-semibold text-emerald-700 dark:text-emerald-400">Task Management</h2>
                                <p class="mt-1 text-emerald-600 dark:text-emerald-300">You have task management permissions.</p>
                                <div class="mt-3">
                                    <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        Manage Tasks
                                    </a>
                                </div>
                            </div>
                        @endif
                        @if(Auth::user()->isVPNclient())
                            <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg">
                                <h2 class="text-lg font-semibold text-blue-700 dark:text-blue-400">VPN Access</h2>
                                <p class="mt-1 text-blue-600 dark:text-blue-300">You have VPN client access.</p>
                            </div>
                        @endif

                        @if(Auth::user()->isEmailPermission())
                            <div class="bg-amber-50 dark:bg-amber-900/30 p-4 rounded-lg">
                                <h2 class="text-lg font-semibold text-amber-700 dark:text-amber-400">Email Management</h2>
                                <p class="mt-1 text-amber-600 dark:text-amber-300">You have email management permissions.</p>
                            </div>
                        @endif
                        <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                            <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Your Account</h2>
                            <p class="mt-1 text-gray-600 dark:text-gray-400">View and manage your account details.</p>
                            <div class="mt-3">
                                <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-300 focus:bg-gray-700 dark:focus:bg-gray-300 active:bg-gray-900 dark:active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Profile
                                </a>
                                
                                <!-- Logout Button -->
                                <form method="POST" action="{{ route('logout') }}" class="inline-block ml-2">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Welcome</h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Please log in to access your dashboard.</p>
                        <div class="mt-4">
                            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Log in
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </body>
</html>

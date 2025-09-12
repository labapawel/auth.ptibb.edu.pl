@extends('admin.layouts.app')
@section('content')
    <div class="dashboard-container p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Statystyki -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Użytkownicy DB</h3>
                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ \App\Models\User::count() }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Użytkownicy w bazie danych</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Użytkownicy LDAP</h3>
                @php
                    try {
                        $ldapUsersCount = \App\Ldap\User::all()->count();
                    } catch (\Exception $e) {
                        $ldapUsersCount = 'N/A';
                    }
                @endphp
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $ldapUsersCount }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Użytkownicy LDAP</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Grupy LDAP</h3>
                @php
                    try {
                        $ldapGroupsCount = \App\Ldap\Group::all()->count();
                    } catch (\Exception $e) {
                        $ldapGroupsCount = 'N/A';
                    }
                @endphp
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $ldapGroupsCount }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Grupy LDAP</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">VPN</h3>
                <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ \App\Models\User::where('permission', 1)->count() }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Klienci VPN</p>
            </div>
        </div>

        <!-- Szybkie akcje LDAP -->
        <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Zarządzanie LDAP</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('ldap.users.index') }}" class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Użytkownicy LDAP</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Zarządzaj kontami użytkowników</p>
                        </div>
                    </a>

                    <a href="{{ route('ldap.groups.index') }}" class="flex items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Grupy LDAP</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Zarządzaj grupami użytkowników</p>
                        </div>
                    </a>

                    <a href="{{ route('ldap.users.create') }}" class="flex items-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Nowy użytkownik</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Dodaj nowe konto LDAP</p>
                        </div>
                    </a>

                    <a href="{{ route('ldap.groups.create') }}" class="flex items-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Nowa grupa</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Utwórz nową grupę LDAP</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Ostatnie aktywności -->
        <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Ostatnie aktywności</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Użytkownik</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Akcja</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Data</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <!-- Tutaj będą wyświetlane rzeczywiste dane -->
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Brak danych</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

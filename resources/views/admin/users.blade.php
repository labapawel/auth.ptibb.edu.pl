
{{-- filepath: resources/views/admin/users/index.blade.php --}}
@extends('admin::layout')

@section('content')
    <div class="users-container p-6">
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Zarządzanie Użytkownikami</h2>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Dodaj Użytkownika
            </button>
        </div>

        <!-- Filtry -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Szukaj</label>
                    <input type="text" id="search" name="search" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Imię, email...">
                </div>
                <div>
                    <label for="permission" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Uprawnienia</label>
                    <select id="permission" name="permission" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Wszystkie</option>
                        <option value="1">VPN</option>
                        <option value="2">Zadania</option>
                        <option value="4">PC</option>
                        <option value="8">Email</option>
                        <option value="16">Admin</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select id="status" name="status" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Wszystkie</option>
                        <option value="active">Aktywny</option>
                        <option value="inactive">Nieaktywny</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Lista użytkowników -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Imię</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uprawnienia</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Akcje</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach(\App\Models\User::all() as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center text-gray-500 dark:text-gray-300">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @if($user->isVPNclient())
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">VPN</span>
                                    @endif
                                    @if($user->isTaskPermission())
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Zadania</span>
                                    @endif
                                    @if($user->isAdmin())
                                    <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Admin</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Aktywny</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edytuj</button>
                                    <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Usuń</button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <!-- Paginacja -->
                <div class="flex justify-between">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Wyświetlanie 1-10 z {{ \App\Models\User::count() }} wyników
                    </div>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Poprzednia</button>
                        <button class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">Następna</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

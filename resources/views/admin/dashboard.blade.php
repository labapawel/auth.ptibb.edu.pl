<?php
@extends('admin::layout')
@section('content')
    <div class="dashboard-container p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Statystyki -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Użytkownicy</h3>
                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ \App\Models\User::count() }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Całkowita liczba użytkowników</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">VPN</h3>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ \App\Models\User::where('permission', 1)->count() }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Klienci VPN</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Zadania</h3>
                <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">0</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Aktywne zadania</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">PC</h3>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">0</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Komputery</p>
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
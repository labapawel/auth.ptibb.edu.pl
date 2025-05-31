@extends('admin.layouts.app')

@section('title', 'Usuń użytkownika LDAP')

@section('content')
<div class="admin-container">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Usuń użytkownika LDAP</h2>
        <a href="{{ route('admin.ldap.users.index') }}" class="admin-btn-secondary">Powrót</a>
    </div>
    
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif    <div class="admin-card">
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Czy na pewno chcesz usunąć tego użytkownika LDAP?</h3>
            
            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-md border border-red-200 dark:border-red-800 mb-6">
                <p class="text-red-600 dark:text-red-400">
                    <strong>Uwaga:</strong> Ta operacja jest nieodwracalna. Użytkownik zostanie całkowicie usunięty z katalogu LDAP.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">CN:</p>
                    <p class="text-base text-gray-900 dark:text-white">{{ $user->cn }}</p>
                </div>
                
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Imię:</p>
                    <p class="text-base text-gray-900 dark:text-white">{{ $user->givenName }}</p>
                </div>
                
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nazwisko:</p>
                    <p class="text-base text-gray-900 dark:text-white">{{ $user->sn }}</p>
                </div>
                
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email:</p>
                    <p class="text-base text-gray-900 dark:text-white">{{ $user->mail }}</p>
                </div>
                
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Login (SAM):</p>
                    <p class="text-base text-gray-900 dark:text-white">{{ $user->samaccountname }}</p>
                </div>
                
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Distinguished Name:</p>
                    <p class="text-base text-gray-900 dark:text-white">{{ $user->distinguishedname }}</p>
                </div>
            </div>

            <form action="{{ route('admin.ldap.users.destroy', $user->distinguishedname) }}" method="POST" class="mt-6">
                @csrf
                @method('DELETE')
                  <div class="flex gap-4">
                    <button type="submit" class="admin-btn-danger">
                        Tak, usuń użytkownika
                    </button>
                    
                    <a href="{{ route('admin.ldap.users.index') }}" class="admin-btn-secondary">
                        Anuluj
                    </a>
                </div>
            </form>        </div>
    </div>
</div>
@endsection

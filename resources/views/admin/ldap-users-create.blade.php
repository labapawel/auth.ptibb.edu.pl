
@extends('admin.layouts.app')

@section('title', 'Dodaj użytkownika LDAP')

@section('content')
<div class="admin-container">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Dodaj użytkownika LDAP</h2>
        <a href="{{ route('admin.ldap.users.index') }}" class="admin-btn-secondary">Powrót</a>
    </div>
    
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif    <div class="admin-card">
        <form action="{{ route('admin.ldap.users.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">                <div>
                    <label for="cn" class="admin-form-label">CN (Common Name)</label>
                    <input type="text" id="cn" name="cn" value="{{ old('cn') }}" class="admin-form-input" required>
                    @error('cn')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>                <div>
                    <label for="givenName" class="admin-form-label">Imię</label>
                    <input type="text" id="givenName" name="givenName" value="{{ old('givenName') }}" class="admin-form-input" required>
                    @error('givenName')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>                <div>
                    <label for="sn" class="admin-form-label">Nazwisko</label>
                    <input type="text" id="sn" name="sn" value="{{ old('sn') }}" class="admin-form-input" required>
                    @error('sn')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>                <div>
                    <label for="mail" class="admin-form-label">Email</label>
                    <input type="email" id="mail" name="mail" value="{{ old('mail') }}" class="admin-form-input" required>
                    @error('mail')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>                <div>
                    <label for="samAccountName" class="admin-form-label">Login (SAM Account Name)</label>
                    <input type="text" id="samAccountName" name="samAccountName" value="{{ old('samAccountName') }}" class="admin-form-input" required>
                    @error('samAccountName')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>                <div>
                    <label for="password" class="admin-form-label">Hasło</label>
                    <input type="password" id="password" name="password" class="admin-form-input" required>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>            <div class="mt-6">
                <button type="submit" class="admin-btn">
                    Dodaj użytkownika
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

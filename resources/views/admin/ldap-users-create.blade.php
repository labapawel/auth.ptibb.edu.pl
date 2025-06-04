@extends('admin.layouts.app')

@section('title', 'Dodaj użytkownika LDAP')

@section('content')
<div class="admin-container">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Dodaj użytkownika LDAP</h2>
        <a href="/admin/ldap/users" class="admin-btn-secondary">Powrót</a>
    </div>
    
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif    <div class="admin-card">
        <form action="/admin/ldap/users/"method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="cn" class="admin-form-label">CN (Common Name)</label>
                    <input type="text" id="cn" name="cn" value="{{ old('cn') }}" class="admin-form-input" required>
                    @error('cn')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="givenname" class="admin-form-label">Imię</label>
                    <input type="text" id="givenname" name="givenname" value="{{ old('givenname') }}" class="admin-form-input" required>
                    @error('givenname')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="sn" class="admin-form-label">Nazwisko</label>
                    <input type="text" id="sn" name="sn" value="{{ old('sn') }}" class="admin-form-input" required>
                    @error('sn')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="mail" class="admin-form-label">Email</label>
                    <input type="email" id="mail" name="mail" value="{{ old('mail') }}" class="admin-form-input" required>
                    @error('mail')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="uid" class="admin-form-label">Login (UID)</label>
                    <input type="text" id="uid" name="uid" value="{{ old('uid') }}" class="admin-form-input" required>
                    @error('uid')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="uidnumber" class="admin-form-label">UID Number</label>
                    <input type="number" id="uidnumber" name="uidnumber" value="{{ old('uidnumber') }}" class="admin-form-input" required>
                    @error('uidnumber')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="gidnumber" class="admin-form-label">GID Number</label>
                    <input type="number" id="gidnumber" name="gidnumber" value="{{ old('gidnumber') }}" class="admin-form-input" required>
                    @error('gidnumber')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="userpassword" class="admin-form-label">Hasło</label>
                    <input type="password" id="userpassword" name="userpassword" class="admin-form-input" required>
                    @error('userpassword')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Ukryte pola, ustawiane automatycznie w kontrolerze -->
                <input type="hidden" id="homedirectory" name="homedirectory" value="">
                <input type="hidden" id="loginshell" name="loginshell" value="">
            </div>            <div class="mt-6">
                <button type="submit" class="admin-btn">
                    Dodaj użytkownika
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

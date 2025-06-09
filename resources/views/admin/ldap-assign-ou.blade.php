@extends('admin.layouts.app')

@section('title', 'Przypisz użytkownika do grupy')

@section('content')
<div class="admin-container max-w-lg mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6">Przypisz użytkownika do grupy (klasy)</h2>
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif
    <form method="POST" action="{{ route('admin.ldap.users.assignGroup', $userDn) }}">
        @csrf
        <div class="mb-4">
            <label for="group_cn" class="block text-sm font-medium text-gray-700">Wybierz grupę (klasę)</label>
            <input type="text" id="group_cn" name="group_cn" class="admin-form-input" required placeholder="np. 2025a">
        </div>
        <button type="submit" class="admin-btn">Przypisz do grupy</button>
    </form>
</div>
@endsection

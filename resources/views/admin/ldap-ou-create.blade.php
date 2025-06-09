@extends('admin.layouts.app')

@section('title', 'Dodaj grupę (klasę) LDAP')

@section('content')
<div class="admin-container max-w-lg mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6">Dodaj nową grupę (klasę)</h2>
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif
    <form method="POST" action="{{ route('ldap.organizational-units.store') }}">
        @csrf
        <div class="mb-4">
            <label for="organizational_unit" class="block text-sm font-medium text-gray-700">Nazwa jednostki organizacyjnej (np. 2025a)</label>
            <input type="text" id="organizational_unit" name="organizational_unit" class="admin-form-input" required>
        </div>
        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700">Opis (opcjonalnie)</label>
            <input type="text" id="description" name="description" class="admin-form-input">
        </div>
        <div class="mb-4">
            <label for="user-search" class="block text-sm font-medium text-gray-700">Wyszukaj użytkowników</label>
            <input type="text" id="user-search" class="admin-form-input" placeholder="Wpisz nazwę użytkownika...">
        </div>
        <div class="mb-4">
            <label for="users" class="block text-sm font-medium text-gray-700">Wybierz użytkowników do przypisania</label>
            <div id="user-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto border rounded p-2 bg-white">
                @foreach($users as $user)
                    <label class="user-item flex items-center space-x-2 p-2 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="users[]" value="{{ $user->cn }}" class="form-checkbox text-blue-600">
                        <span>{{ $user->cn }} <span class="text-xs text-gray-500">({{ $user->uid }})</span></span>
                    </label>
                @endforeach
            </div>
        </div>
        <button type="submit" class="admin-btn">Utwórz grupę</button>
    </form>
</div>
<script>
    document.getElementById('user-search').addEventListener('input', function (e) {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('#user-list .user-item').forEach(function (item) {
            const userName = item.textContent.toLowerCase();
            item.style.display = userName.includes(query) ? '' : 'none';
        });
    });
</script>
@endsection

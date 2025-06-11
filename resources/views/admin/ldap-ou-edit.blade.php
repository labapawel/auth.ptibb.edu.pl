@extends('admin.layouts.app')

@section('title', 'Edytuj jednostkę organizacyjną LDAP')

@section('content')
<div class="admin-container max-w-2xl mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6">Edytuj jednostkę organizacyjną LDAP</h2>

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif    <form method="POST" action="{{ route('ldap.organizational-units.update', $organizationalUnit['ou'][0]) }}">
        @csrf
        @method('PUT')
        
        <div class="mb-4">
            <label for="ou" class="admin-form-label">OU (Organizational Unit)</label>
            <input type="text" id="ou" name="ou" class="admin-form-input" value="{{ $organizationalUnit['ou'][0] }}" readonly disabled>
            <p class="text-sm text-gray-500">Nazwa jednostki organizacyjnej nie może być zmieniona</p>
        </div>

        <div class="mb-4">
            <label for="description" class="admin-form-label">Opis</label>
            <input type="text" id="description" name="description" class="admin-form-input" value="{{ old('description', $organizationalUnit['description'][0] ?? '') }}">
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="user-search" class="admin-form-label">Wyszukaj użytkowników</label>
            <input type="text" id="user-search" class="admin-form-input" placeholder="Wpisz nazwę użytkownika...">
        </div>        <div class="mb-4">
            <label for="users" class="admin-form-label">Przypisz użytkowników</label>
            <div id="user-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto border rounded p-2 bg-white">
                @foreach($users as $user)
                    <label class="user-item flex items-center space-x-2 p-2 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="users[]" value="{{ $user['uid'] }}" class="form-checkbox text-blue-600"
                               @if(isset($organizationalUnit['member']) && in_array($user['uid'], $organizationalUnit['member'])) checked @endif>
                        <span>{{ $user['cn'] }} <span class="text-xs text-gray-500">({{ $user['uid'] }})</span></span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="admin-btn">Zaktualizuj jednostkę organizacyjną</button>
            <a href="{{ route('ldap.organizational-units.index') }}" class="admin-btn-secondary">Anuluj</a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('user-search');
        const userList = document.getElementById('user-list');
        if (!searchInput || !userList) return;

        searchInput.addEventListener('input', function (e) {
            const query = e.target.value.toLowerCase();
            const userItems = userList.querySelectorAll('.user-item');
            
            userItems.forEach(function (item) {
                const userName = item.textContent.toLowerCase();
                item.style.display = userName.includes(query) ? '' : 'none';
            });
        });
    });
</script>

@endsection

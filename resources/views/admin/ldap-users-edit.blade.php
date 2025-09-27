@extends('admin.layouts.app')

@section('title', 'Edytuj użytkownika LDAP')

@section('content')
<div class="admin-container">
    <div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold mb-6">Edytuj użytkownika LDAP</h2>
                       <a href="{{ route('ldap.users.index') }}"><button class="admin-btn-secondary">Anuluj</button></a>

    </div>

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif
    <form method="POST" action="{{ route('ldap.users.update', $user->uid) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label for="cn" class="admin-form-label">CN (Common Name)</label>
                <input type="text" id="cn" name="cn" class="admin-form-input" value="{{ old('cn', $user->cn[0]) }}" required>
                @error('cn')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="uid" class="admin-form-label">UID (Login)</label>
                <input type="text" id="uid" name="uid" class="admin-form-input" value="{{ $user->uid[0] }}" readonly disabled>
                <p class="text-sm text-gray-500">UID nie może być zmieniony</p>
            </div>

            <div class="mb-4">
                <label for="givenname" class="admin-form-label">Imię</label>
                <input type="text" id="givenname" name="givenname" class="admin-form-input" value="{{ old('givenname', $user->givenname[0]) }}" required>
                @error('givenname')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="sn" class="admin-form-label">Nazwisko</label>
                <input type="text" id="sn" name="sn" class="admin-form-input" value="{{ old('sn', $user->sn[0]) }}" required>
                @error('sn')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4 md:col-span-2">
                <label for="mail" class="admin-form-label">Email</label>
                <input type="email" id="mail" name="mail" class="admin-form-input" value="{{ old('mail', $user->mail[0]) }}" required>
                @error('mail')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>        <div class="mb-4">
            <label for="group-search" class="admin-form-label">Wyszukaj grupy</label>
            <input type="text" id="group-search" class="admin-form-input" placeholder="Wpisz nazwę grupy...">
        </div>

        <div class="mb-4">
            <label for="groups" class="admin-form-label">Przypisz do grup</label>
            <div id="group-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto border rounded p-2 bg-white">
                
                @foreach($groups as $group)
                <label class="group-item flex items-center space-x-2 p-2 rounded hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="groups[]" value="{{ $group->cn }}" class="form-checkbox text-blue-600"
                    @if(in_array($user->uid[0], $group->members)) checked @endif>
                    <span>{{ $group->cn }} @if($group->description)<span class="text-xs text-gray-500">({{ $group->description}})</span>@endif</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="admin-btn">Zaktualizuj użytkownika</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('organizational-unit-search').addEventListener('input', function (e) {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('#ou-list .ou-item').forEach(function (item) {
            const ouName = item.textContent.toLowerCase();
            item.style.display = ouName.includes(query) ? '' : 'none';
        });
    });
</script>
@endsection

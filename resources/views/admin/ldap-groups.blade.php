@extends('admin.layouts.app')

@section('title', 'Grupy LDAP')

@section('content')
<div class="admin-container">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Grupy LDAP</h2>
        <a href="{{ route('ldap.groups.create') }}" class="btn btn-primary">
            Dodaj grupę
        </a>
    </div>
    
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if($groups === [])
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded" role="alert">
            <p>Brak grup LDAP do wyświetlenia.</p>
        </div>
    @else
        <div class="admin-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-header">
                        <tr>
                            <th class="admin-table-head">Nazwa grupy</th>
                            <th class="admin-table-head">Opis</th>
                            <th class="admin-table-head">GID</th>
                            <th class="admin-table-head">Członkowie</th>
                            <th class="admin-table-head">Akcje</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($groups as $group)
                        <tr>
                            <td class="admin-table-cell">{{ $group->cn }}</td>
                    <td class="admin-table-cell">{{ $group->description ?? '-' }}</td>
                    <td class="admin-table-cell">{{ $group->gidnumber }}</td>
                    <td class="admin-table-cell">{{ $group->memberCount }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('ldap.groups.show', $group->cn) }}" class="text-blue-600 hover:text-blue-900 mr-3">Podgląd</a>
                                <a href="{{ route('ldap.groups.edit', $group->cn) }}" class="text-green-600 hover:text-green-900 mr-3">Edytuj</a>
                                <form method="POST" action="{{ route('ldap.groups.destroy', $group->cn) }}" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Czy na pewno chcesz usunąć tę grupę?')">Usuń</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
